<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Twilio\Rest\Client;

class RunFirePrediction extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:run-fire-prediction {file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $tmpfile = $this->argument('file');
        if (!file_exists($tmpfile)) {
            $this->error('Input file missing');
            return 1;
        }

        $json = json_decode(file_get_contents($tmpfile), true);
        if (!$json) {
            unlink($tmpfile);
            return 1;
        }

        $callSid = $json['call_sid'] ?? time();
        $payload = $json['payload'] ?? [];
        $phone = $json['phone'] ?? '';
        $lang = $json['lang'] ?? 'en';

        // Python path
        $pyPath = base_path('backend/predict_fire.py');
        $this->info("Running prediction for Call SID: $callSid, Phone: $phone, ". json_encode($json));
        $cmd = 'python3 ' . escapeshellarg($pyPath);

        $descriptorspec = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ];

        $proc = proc_open($cmd, $descriptorspec, $pipes, base_path('backend'));
        if (is_resource($proc)) {
            fwrite($pipes[0], json_encode($payload));
            fclose($pipes[0]);

            $output = stream_get_contents($pipes[1]);
            fclose($pipes[1]);

            $err = stream_get_contents($pipes[2]);
            fclose($pipes[2]);

            $ret = proc_close($proc);
        } else {
            $output = null;
            $err = 'proc_open failed';
            $ret = 1;
        }

        // Parse result and update call
        $result = json_decode($output, true);
        if ($result && isset($result['fire_risk'])) {
            $fire_risk = $result['fire_risk'] == 1 ? 'Fire' : 'No fire';
            $probability = $result['probability'] ?? 0;
            $query = "lang=$lang&step=tell_result&success=1&fire_risk=$fire_risk&probability=$probability";
            $query = [
                'lang' => $lang,
                'step' => 'tell_result',
                'success' => 1,
                'fire_risk' => $fire_risk,
                'probability' => $probability
            ];
            $this->info(json_encode($query));
            // $this->info("Prediction completed: $query for Call SID: $callSid, Phone: $phone");

        } else {
            $err = $err ?: 'Unknown error';
            $query = `lang=$lang&step=tell_result&success=0&error=$err`;
            $query = [
                'lang' => $lang,
                'step' => 'tell_result',
                'success' => 0,
                'error' => $err
            ];
            $this->error(json_encode($query));
        }

        $this->updateCall($callSid, $query);

        // Clean up input
        unlink($tmpfile);

        return 0;
    }

    protected function updateCall($callSid, array $params)
    {
        $sid = env("TWILIO_SID");
        $token = env("TWILIO_AUTH_TOKEN");
        $appUrl = env('APP_URL');

        if (!$sid || !$token) {
            return false;
        }
        if (!$appUrl) {
            return false;
        }

        // Build the public menu URL
        $base = rtrim($appUrl, '/').'/menu';
        $query = http_build_query($params, '', '&', PHP_QUERY_RFC3986);
        $menuUrl = $base . '?' . $query;

        $client = new \Twilio\Rest\Client($sid, $token);

        // STEP 1: fetch call to see if Twilio knows about it & its status
        try {
            $callInfo = $client->calls($callSid)->fetch();
        } catch (\Throwable $e) {
            // continue anyway — but this is suspicious if fetch fails
        }

        // STEP 2: try SDK update
        try {
            $call = $client->calls($callSid)->update([
                'url' => $menuUrl,
                'method' => 'GET'
            ]);
            return true;
        } catch (\Throwable $e) {
            // SDK threw — log full details and fallback to raw HTTP
            \Illuminate\Support\Facades\Log::error('updateCall.sdk_error', ['err' => $e->getMessage()]);
        }

        // STEP 3: fallback — raw HTTP POST so you can see HTTP status + body
        try {
            $twilioApi = "https://api.twilio.com/2010-04-01/Accounts/$sid/Calls/$callSid.json";

            $response = \Illuminate\Support\Facades\Http::withBasicAuth($sid, $token)
                ->asForm()
                ->post($twilioApi, [
                    'Url' => $menuUrl,
                    'Method' => 'GET'
                ]);

            return $response->successful();
        } catch (\Throwable $e) {
            return false;
        }
    }



}
