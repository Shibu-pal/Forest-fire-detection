<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Twilio\TwiML\VoiceResponse;
use Twilio\Rest\Client;

class IVRController extends Controller
{
    // --- Voice / language mapping ---
    protected function voiceMap(): array
    {
        return [
            'en' => ['voice' => 'Google.en-IN-Standard-E', 'lang' => 'en-IN'],
            'hi' => ['voice' => 'Google.hi-IN-Standard-E', 'lang' => 'hi-IN'],
            'bn' => ['voice' => 'Google.bn-IN-Standard-A', 'lang' => 'bn-IN'],
        ];
    }

    protected function voiceFor(string $lang): string
    {
        return $this->voiceMap()[$lang]['voice'] ?? $this->voiceMap()['en']['voice'];
    }

    protected function langCode(string $lang): string
    {
        return $this->voiceMap()[$lang]['lang'] ?? $this->voiceMap()['en']['lang'];
    }

    // --- Localized phrases ---
    protected function t(string $key, string $lang): string
    {
        $strings = [
            'choose_language' => [
                'en' => 'Welcome to Fire Prediction. Press 1 for English. Press 2 for Hindi. Press 3 for Bengali.',
                'hi' => 'फायर प्रेडिक्शन में आपका स्वागत है। अंग्रेज़ी के लिये 1 दबाएँ। हिंदी के लिये 2। बंगाली के लिये 3।',
                'bn' => 'ফায়ার প্রেডিকশনে স্বাগতম। ইংরেজির জন্য 1 চাপুন। হিন্দির জন্য 2। বাংলার জন্য 3।',
            ],
            'no_input' => [
                'en' => 'There was no input detected. Please try again.',
                'hi' => 'कोई इनपुट नहीं मिला। कृपया पुनः प्रयास करें।',
                'bn' => 'কোনো ইনপুট সনাক্ত করা যায়নি। অনুগ্রহ করে আবার চেষ্টা করুন।',
            ],
            'invalid_option' => [
                'en' => 'Invalid option. Please try again.',
                'hi' => 'अमान्य विकल्प। कृपया पुनः प्रयास करें।',
                'bn' => 'অবৈধ অপশন। অনুগ্রহ করে আবার চেষ্টা করুন।',
            ],
            'enter_temperature' => [
                'en' => 'Enter temperature in degrees Celsius, then press the star key.',
                'hi' => 'डिग्री सेल्सियस में तापमान दर्ज करें, फिर स्टार की दबाएँ।',
                'bn' => 'ডিগ্রী সেলসিয়াসে তাপমাত্রা প্রদান করুন, তারপর স্টার কী চাপুন।',
            ],
            'enter_humidity' => [
                'en' => 'Enter relative humidity as whole percent, then press the star key.',
                'hi' => 'सापेक्ष आर्द्रता पूरे प्रतिशत में दर्ज करें, फिर स्टार की दबाएँ।',
                'bn' => 'আপেক্ষিক আর্দ্রতা পূর্ণ শতাংশে লিখুন, তারপর স্টার কী চাপুন।',
            ],
            'enter_wind' => [
                'en' => 'Enter wind speed in kilometers per hour, then press the star key.',
                'hi' => 'हवा की गति किलोमीटर प्रति घंटे में दर्ज करें, फिर स्टार की दबाएँ।',
                'bn' => 'বায়ু গতিবেগ কিলোমিটার প্রতি ঘণ্টায় লিখুন, তারপর স্টার কী চাপুন।',
            ],
            'vegetation_options' => [
                'en' => 'Select vegetation type. Press 1 for Coniferous. Press 2 for Deciduous. Press 3 for Grassland. Press 4 for Bamboo. Press 5 for Mixed.',
                'hi' => 'वनस्पति प्रकार चुनें। कॉनिफरस के लिये 1, डेसिड्यूअस के लिये 2, घासभूमि के लिये 3, बाँस के लिये 4, मिश्रित के लिये 5 दबाएँ।',
                'bn' => 'উদ্ভিদ ধরন নির্বাচন করুন। কনিফারাসের জন্য 1, ডেসিডিউসের জন্য 2, ঘাসভূমির জন্য 3, বাঁশের জন্য 4, মিক্সডের জন্য 5 চাপুন।',
            ],
            'enter_elevation' => [
                'en' => 'Enter elevation in meters, then press the star key.',
                'hi' => 'ऊंचाई मीटर में दर्ज करें, फिर स्टार की दबाएँ।',
                'bn' => 'উচ্চতা মিটারে দিন, তারপর স্টার কী চাপুন।',
            ],
            'prediction_failed' => [
                'en' => 'Prediction failed:',
                'hi' => 'पूर्वानुमान विफल हुआ:',
                'bn' => 'পূর্বাভাস ব্যর্থ হয়েছে:',
            ],
            'prediction_result_fire' => [
                'en' => 'Prediction result: Fire.',
                'hi' => 'पूर्वानुमान परिणाम: आग।',
                'bn' => 'পূর্বাভাস ফলাফল: আগুন।',
            ],
            'prediction_result_no_fire' => [
                'en' => 'Prediction result: No fire, With probability .',
                'hi' => 'पूर्वानुमान परिणाम: आग नहीं, संभावना ',
                'bn' => 'পূর্বাভাস ফলাফল: আগুন নেই, সম্ভাবনা ',
            ],
            'post_result_prompt' => [
                'en' => 'Press 1 to start again. Press 2 to hang up.',
                'hi' => 'फिर से शुरू करने के लिये 1 दबाएँ। कॉल समाप्त करने के लिये 2 दबाएँ।',
                'bn' => 'আবার শুরু করতে 1 চাপুন। কল শেষ করতে 2 চাপুন।',
            ],
            'goodbye' => [
                'en' => 'Goodbye.',
                'hi' => 'अलविदा।',
                'bn' => 'বিদায়।',
            ],
            'previous_inputs_summary' => [
                'en' => 'You entered: Temperature {temperature} degrees, Humidity {humidity} percent, Wind {wind} kilometers per hour, Vegetation {vegetation}, Elevation {elevation} meters.',
                'hi' => 'आपने दर्ज किया: तापमान {temperature} डिग्री, आर्द्रता {humidity} प्रतिशत, हवा {wind} किलोमीटर प्रति घंटा, वनस्पति {vegetation}, ऊंचाई {elevation} मीटर।',
                'bn' => 'আপনি যা দিয়েছেন: তাপমাত্রা {temperature} ডিগ্রি, আর্দ্রতা {humidity} শতাংশ, বায়ু {wind} কিমি/ঘণ্টা, উদ্ভিদ {vegetation}, উচ্চতা {elevation} মিটার।',
            ],
            'prediction_queue' => [
                'en' => 'Please wait while we process your fire prediction result.',
                'hi' => 'कृपया प्रतीक्षा करें, हम आपकी आग की पूर्वानुमान के परिणाम को संसाधित कर रहे हैं।',
                'bn' => 'অনুগ্রহ করে অপেক্ষা করুন, আমরা আপনার অগ্নিকাণ্ডের পূর্বাভাসের ফলাফল প্রক্রিয়াকরণ করছি।',
            ]
        ];

        $short = substr($lang, 0, 2);
        return $strings[$key][$short] ?? $strings[$key]['en'] ?? '';
    }

    // --- Format inputs into a single spoken sentence ---
    protected function formatInputsForSpeech(array $payload, string $lang): string
    {
        $vegLabel = ucfirst($payload['vegetation_type'] ?? 'mixed');
        $template = $this->t('previous_inputs_summary', $lang);

        $text = str_replace(
            ['{temperature}', '{humidity}', '{wind}', '{vegetation}', '{elevation}'],
            [
                isset($payload['temperature']) ? rtrim(rtrim(number_format((float)$payload['temperature'], 2, '.', ''), '0'), '.') : '0',
                isset($payload['humidity']) ? intval($payload['humidity']) : '0',
                isset($payload['wind_speed']) ? rtrim(rtrim(number_format((float)$payload['wind_speed'], 2, '.', ''), '0'), '.') : '0',
                $vegLabel,
                isset($payload['elevation']) ? rtrim(rtrim(number_format((float)$payload['elevation'], 2, '.', ''), '0'), '.') : '0',
            ],
            $template
        );

        return $text;
    }

    // --- Entry point: language selection ---
    public function showLanguageMenu(Request $request)
    {
        $response = new VoiceResponse();

        $gather = $response->gather([
            'numDigits' => 1,
            'action' => route('ivr.menu',['step'=>'temperature']),
            'method' => 'POST',
            'timeout' => 10,
        ]);

        $gather->say($this->t('choose_language', 'en'), ['voice' => $this->voiceFor('en'), 'language' => $this->langCode('en')]);
        $response->say($this->t('no_input', 'en'), ['voice' => $this->voiceFor('en'), 'language' => $this->langCode('en')]);
        $response->redirect(route('ivr.welcome'), ['method' => 'POST']);

        return response($response)->header('Content-Type', 'application/xml');
    }

    // --- Central handler: main menu + step flow ---
    public function showMenuResponse(Request $request)
    {
        $lang = $request->input('lang', 'en'); // short keys: en, hi, bn
        $voice = $this->voiceFor($lang);
        $languageCode = $this->langCode($lang);
        $digits = $request->input('Digits', null);
        $step = $request->input('step', null);
        $fresh_query = $request->query();
        unset($fresh_query['Digits']);
        $response = new VoiceResponse();

        // After prediction: handle post-result choices (step = post_result)
        if ($step === 'post_result') {
            
        }

        // Step-based flows with '*' as finish key and retries
        switch ($step) {
            case 'temperature':
                switch ($digits) {
                    case '1':
                    case 'en':
                         $lang = 'en'; break;
                    case '2':
                    case 'hi':
                        $lang = 'hi'; break;
                    case '3':
                    case 'bn':
                        $lang = 'bn'; break;
                    default:
                        $response->say($this->t('invalid_option', 'en'), ['voice' => $this->voiceFor('en'), 'language' => $this->langCode('en')]);
                        $response->redirect(route('ivr.welcome'), ['method' => 'POST']);
                        return response($response)->header('Content-Type', 'application/xml');
                }
                $voice = $this->voiceFor($lang);
                $languageCode = $this->langCode($lang);
                $g = $response->gather([
                    'finishOnKey' => '*',
                    'action' => route('ivr.menu', ['lang' => $lang, 'step' => 'humidity']),
                    'method' => 'POST',
                    'timeout' => 10,
                ]);
                $g->say($this->t('enter_temperature', $lang), ['voice' => $voice, 'language' => $languageCode]);
                $response->say($this->t('no_input', $lang), ['voice' => $voice, 'language' => $languageCode]);
                $response->redirect(route('ivr.menu', array_merge($request->query(), ['Digits' => $lang])), ['method' => 'POST']);
                break;
            case 'humidity':
                $temp = trim($request->input('Digits', ''));
                if (!preg_match('/^-?\d{1,3}$/', $temp)) {
                    $response->say($this->t('invalid_option', $lang), ['voice' => $voice, 'language' => $languageCode]);
                    $response->redirect(route('ivr.menu', array_merge($fresh_query, ['step' => 'temperature', 'Digits' => $lang])), ['method' => 'POST']);
                    return response($response)->header('Content-Type', 'application/xml');
                }
                $g = $response->gather([
                    'finishOnKey' => '*',
                    'action' => route('ivr.menu', array_merge($fresh_query, ['temperature' => $temp, 'step' => 'wind'])),
                    'method' => 'POST',
                    'timeout' => 10,
                ]);
                $g->say($this->t('enter_humidity', $lang), ['voice' => $voice, 'language' => $languageCode]);
                $response->say($this->t('no_input', $lang), ['voice' => $voice, 'language' => $languageCode]);
                $response->redirect(route('ivr.menu', $request->query()), ['method' => 'POST']);
                break;
            case 'wind':
                    $hum = trim($request->input('Digits', ''));
                    if (!preg_match('/^\d{1,3}$/', $hum) || intval($hum) < 0 || intval($hum) > 100) {
                        $response->say($this->t('invalid_option', $lang), ['voice' => $voice, 'language' => $languageCode]);
                        $response->redirect(route('ivr.menu', array_merge($fresh_query, ['lang' => $lang, 'step' => 'humidity', 'Digits' => $request->query('temperature')])), ['method' => 'POST']);
                        return response($response)->header('Content-Type', 'application/xml');
                    }
                    $g = $response->gather([
                        'finishOnKey' => '*',
                        'action' => route('ivr.menu', array_merge($fresh_query, ['humidity' => $hum, 'step' => 'vegetation'])),
                        'method' => 'POST',
                        'timeout' => 10,
                    ]);
                $g->say($this->t('enter_wind', $lang), ['voice' => $voice, 'language' => $languageCode]);
                $response->say($this->t('no_input', $lang), ['voice' => $voice, 'language' => $languageCode]);
                $response->redirect(route('ivr.menu', $request->query()), ['method' => 'POST']);
                break;
            case 'vegetation':
                $wind = trim($request->input('Digits', ''));
                if (!preg_match('/^\d{1,3}$/', $wind)) {
                    $response->say($this->t('invalid_option', $lang), ['voice' => $voice, 'language' => $languageCode]);
                    $response->redirect(route('ivr.menu', array_merge($fresh_query, ['step' => 'wind', 'Digits' => $request->query('humidity')])), ['method' => 'POST']);
                    return response($response)->header('Content-Type', 'application/xml');
                }
                $g = $response->gather([
                    'numDigits' => 1,
                    'action' => route('ivr.menu', array_merge($fresh_query, ['wind' => $wind, 'step' => 'elevation'])),
                    'method' => 'POST',
                    'timeout' => 10,
                ]);
                $g->say($this->t('vegetation_options', $lang), ['voice' => $voice, 'language' => $languageCode]);
                $response->say($this->t('no_input', $lang), ['voice' => $voice, 'language' => $languageCode]);
                $response->redirect(route('ivr.menu', $request->query()), ['method' => 'POST']);
                break;
            case 'elevation':
                $veg = trim($request->input('Digits', ''));
                if ($veg === '' || !in_array(intval($veg), [1,2,3,4,5])) {
                    $response->say($this->t('invalid_option', $lang), ['voice' => $voice, 'language' => $languageCode]);
                    $response->redirect(route('ivr.menu', array_merge($fresh_query, ['step' => 'vegetation', 'Digits' => $request->query('wind')])), ['method' => 'POST']);
                    return response($response)->header('Content-Type', 'application/xml');
                }
                $g = $response->gather([
                    'finishOnKey' => '*',
                    'action' => route('ivr.menu', array_merge($fresh_query, ['vegetation' => $veg, 'step' => 'save_elevation'])),
                    'method' => 'POST',
                    'timeout' => 10,
                ]);
                $g->say($this->t('enter_elevation', $lang), ['voice' => $voice, 'language' => $languageCode]);
                $response->say($this->t('no_input', $lang), ['voice' => $voice, 'language' => $languageCode]);
                $response->redirect(route('ivr.menu', $request->query()), ['method' => 'POST']);
                break;
            case 'save_elevation':
                $elev = trim($request->input('Digits', ''));
                if ($elev === '' || !preg_match('/^\-?\d{1,5}$/', $elev)) {
                    $response->say($this->t('invalid_option', $lang), ['voice' => $voice, 'language' => $languageCode]);
                    $response->redirect(route('ivr.menu', array_merge($fresh_query, ['step' => 'elevation', 'Digits' => $request->query('vegetation')])), ['method' => 'POST']);
                    return response($response)->header('Content-Type', 'application/xml');
                }

                // Build payload from collected query params + this elevation
                $payload = [
                    'temperature' => floatval($request->query('temperature', 0)),
                    'humidity' => floatval($request->query('humidity', 0)),
                    'wind_speed' => floatval($request->query('wind', 0)),
                    'vegetation_type' => $this->vegetationOutput($request->query('vegetation', '')),
                    'elevation' => floatval($elev),
                ];

                // Speak summary
                $response->say($this->formatInputsForSpeech($payload, $lang), ['voice' => $voice, 'language' => $languageCode]);
                $response->redirect(route('ivr.menu', array_merge($fresh_query, ['lang' => $lang, 'step' => 'run_prediction', 'payload' => $payload])), ['method' => 'POST']);
                break;
            case 'run_prediction':
                $payload = $request->query('payload');
                // Save payload to temp file and run prediction asynchronously
                $tmpDir = storage_path('app/tmp');
                if (!is_dir($tmpDir)) {
                    mkdir($tmpDir, 0777, true);
                }

                $tmpfile = $tmpDir.'/tmp'.rand(1,100).'.json';
                $data = [
                    'call_sid' => $request->input('CallSid'),
                    'payload' => $payload,
                    'phone' => $request->input('From'),
                    'lang' => $lang
                ];
                file_put_contents($tmpfile, json_encode($data));

                // Run prediction command asynchronously
                $command = 'php "' . base_path('artisan') . '" app:run-fire-prediction ' . escapeshellarg($tmpfile);
                if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                    pclose(popen("start /B cmd /C " . $command . " > NUL 2>&1", "r"));
                } else {
                    shell_exec($command . ' > /dev/null 2>&1 &');
                }

                $response->say($this->t('prediction_queue', $lang), ['voice' => $voice, 'language' => $languageCode, 'loop' => 0]);
                // Enqueue the call to wait for result
                $response->enqueue('prediction_queue');
                break;

            case 'tell_result':
                $success = $request->query('success');
                $fire_risk = $request->query('fire_risk');
                if($success == 1) {
                    if($fire_risk == 'Fire') {
                        $text = $this->t('prediction_result_fire', $lang).number_format($request->query('probability')*100,1) . $this->percentage($lang).'.';
                    }
                    else {
                        $text = $this->t('prediction_result_no_fire', $lang).number_format($request->query('probability')*100,1) . $this->percentage($lang).'.';
                    }
                    
                    $response->say($text, ['voice' => $voice, 'language' => $languageCode]);
                }
                else {
                    $err = $request->query('error');
                    $text = $this->t('prediction_failed', $lang).$err;
                    $response->say($text, ['voice' => $voice, 'language' => $languageCode]);
                }
                $response->redirect(route('ivr.menu',['lang' => $lang, 'step' => 'post_result']), ['method' => 'POST']);
                break;
            case 'post_result':
                if ($digits === null) {
                    $g = $response->gather([
                        'numDigits' => 1,
                        'action' => route('ivr.menu', ['lang' => $lang, 'step' => 'post_result']),
                        'method' => 'POST',
                        'timeout' => 10,
                    ]);

                    $g->say($this->t('post_result_prompt', $lang), ['voice' => $voice, 'language' => $languageCode]);
                    $response->say($this->t('no_input', $lang), ['voice' => $voice, 'language' => $languageCode]);
                    $response->redirect(route('ivr.menu', ['lang' => $lang, 'step' => 'post_result']), ['method' => 'POST']);
                    return response($response)->header('Content-Type', 'application/xml');
                }

                switch ($digits) {
                    case '1':
                        $response->redirect(route('ivr.menu', ['Digits' => $lang, 'step' => 'temperature']), ['method' => 'POST']);
                        return response($response)->header('Content-Type', 'application/xml');

                    case '2':
                    default:
                        $response->say($this->t('goodbye', $lang), ['voice' => $voice, 'language' => $languageCode]);
                        $response->hangup();
                        return response($response)->header('Content-Type', 'application/xml');
                }
                break;

            default:
                $response->say($this->t('invalid_option', $lang), ['voice' => $voice, 'language' => $languageCode]);
                $response->redirect(route('ivr.menu', ['Digits' => $lang, 'step' => 'temperature']), ['method' => 'POST']);
                break;
        }

        return response($response)->header('Content-Type', 'application/xml');
    }

    // --- Vegetation id -> label ---
    protected function vegetationOutput($data)
    {
        $vegetation = [
            1 => "coniferous",
            2 => "deciduous",
            3 => "grassland",
            4 => "bamboo",
            5 => "mixed",
        ];
        return $vegetation[intval($data)] ?? "mixed";
    }

    protected function percentage($data)
    {
        $percentage = [
            "en" => "percent",
            "bn" => "শতাংশ",
            "hi" => "प्रतिशत"
        ];
        return $percentage[$data] ?? "percent";
    }
}
