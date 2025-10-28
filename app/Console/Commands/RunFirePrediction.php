<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
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

        Log::info('RunFirePrediction started', [
            'file_arg' => $this->argument('file') ?? null,
            'callSid' => $callSid ?? null,
        ]);

        // marker for python invocation
        Storage::put('tmp/prediction_started_'.$callSid.'.txt', json_encode([
            'time' => now()->toDateTimeString(),
            'payload' => $payload
        ]));

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
        

        Log::info('python output', ['out' => $output, 'err' => $err, 'ret' => $ret]);
        Storage::put('tmp/prediction_out_'.$callSid.'.json', $output ?: json_encode(['err'=>$err]));
        $this->updateCall($callSid, $query);

        // Clean up input
        unlink($tmpfile);

        return 0;
    }

    protected function updateCall($callSid, array $params)
    {
        $sid = env("TWILIO_SID");
        $token = env("TWILIO_AUTH_TOKEN");

        if (!$sid || !$token) {
            $this->error('Twilio credentials not configured');
            return;
        }

        $url = "https://api.twilio.com/2010-04-01/Accounts/$sid/Calls/$callSid.json";

        // $this->info("Updating call $callSid with ".json_encode($data));

        // $ch = curl_init();
        // curl_setopt($ch, CURLOPT_URL, $url);
        // curl_setopt($ch, CURLOPT_POST, true);
        // curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        // curl_setopt($ch, CURLOPT_USERPWD, "$sid:$token");
        // curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        // curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        // $response = curl_exec($ch);
        // curl_close($ch);

        // if ($response) {
        //     $this->info("Call updated successfully with result ".$response);
        // } else {
        //     $this->error("Failed to update call");
        // }
        $client = new Client($sid, $token);
        $base = env('APP_URL').'/menu';
        $query = http_build_query($params, '', '&', PHP_QUERY_RFC3986); // rawurlencode each value
        $url = $base . '?' . $query;

        // Option A: Redirect the live call to a URL that returns TwiML
        try {
            $this->info("Redirecting call $callSid to $url");
            $call = $client->calls($callSid)->update([
                'url' => $url,
                'method' => 'GET'
            ]);
            $this->info("Call updated: " . $call->sid);
            return true;
        } catch (\Throwable $e) {
            $this->error("Twilio error: " . $e->getMessage());
            return false;
        }
    
        
    }


}
