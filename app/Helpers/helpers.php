<?php

use App\Enums\ToastType;
use App\Helpers\Dotenv;
use App\Services\EditorBlocksService;
use Hashids\Hashids;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

if (! \function_exists('isAppInstalled')) {
    function isAppInstalled(): bool
    {
        return file_exists(storage_path('installed'));
    }
}

if (! function_exists('is_demo_mode')) {
    /**
     * Check if is demo mode.
     */
    function is_demo_mode(): bool
    {
        return env('DEMO_MODE', true) && getCurrentUser()->email === 'demo@example.com';
    }
}

if (! function_exists('settings')) {
    /**
     * Get app setting stored in db.
     *
     * @param $key
     * @param null $default
     * @return mixed
     */
    function settings($key = null, $default = null)
    {
        $setting = app()->make('App\Services\SettingStorageService');

        if (is_null($key)) {
            return $setting;
        }

        if (is_array($key)) {
            return $setting->set($key);
        }

        return $setting->get($key, value($default));
    }
}

if (! \function_exists('setEnvironmentValue')) {
    /**
     * Function to set or update .env variable.
     */
    function setEnvironmentValue(array $values): bool
    {
        $envFile = app()->environmentFilePath();
        $str = file_get_contents($envFile);

        if (\count($values) > 0) {
            $str .= "\n"; // In case the searched variable is in the last line without \n
            foreach ($values as $envKey => $envValue) {
                if ($envValue === true) {
                    $value = 'true';
                } elseif ($envValue === false) {
                    $value = 'false';
                } else {
                    $value = $envValue;
                }

                $envKey = mb_strtoupper($envKey);
                $keyPosition = mb_strpos($str, "{$envKey}=");
                $endOfLinePosition = mb_strpos($str, "\n", $keyPosition);
                $oldLine = mb_substr($str, $keyPosition, $endOfLinePosition - $keyPosition);
                $space = mb_strpos($value, ' ');
                $envValue = $space === false ? $value : '"'.$value.'"';

                // If key does not exist, add it
                if (! $keyPosition || ! $endOfLinePosition || ! $oldLine) {
                    $str .= "{$envKey}={$envValue}\n";
                } else {
                    $str = str_replace($oldLine, "{$envKey}={$envValue}", $str);
                }
                env($envKey, $envValue);
            }
        }

        $str = mb_substr($str, 0, -1);

        if (! file_put_contents($envFile, $str)) {
            return false;
        }

        return true;
    }
}

if (! \function_exists('setEnv')) {
    /**
     * Function to set or update .env variable.
     */
    function setEnv($key, $value, $quote = false)
    {
        $env = new Dotenv();

        return $env->setKey($key, $value, $quote);
    }
}

if (! \function_exists('getCurrentUser')) {
    /**
     * Function to get current user.
     */
    function getCurrentUser()
    {
        return auth()->user();
    }
}

if (! \function_exists('getCurrentDisk')) {
    /**
     * Function to get current file storage disk.
     */
    function getCurrentDisk(): string
    {
        return settings()->group('advanced')->get('current_file_storage', false);
    }
}

if (! \function_exists('cleanUrl')) {
    /**
     * Clean Url - like google.com.
     */
    function cleanUrl($input)
    {
        return preg_replace('/\b((https?|ftp|file):\/\/|www\.)*/i', '', preg_replace('{/$}', '', urldecode($input)));
    }
}

if (! \function_exists('makeClickableLinks')) {
    /**
     * Make Clickable Links in comment.
     */
    function makeClickableLinks($s)
    {
        return preg_replace('@(https?://([-\w\.]+[-\w])+(:\d+)?(/([\w/_\.#-]*(\?\S+)?[^\.\s])?)?)@', '<a href="$1" class="comment-body" target="_blank">$1</a>', $s);
    }
}

if (! \function_exists('replaceIntentTweet')) {
    /**
     * Twitter intent with % sign is not working because it needs to be encoded.
     */
    function replaceIntentTweet($title)
    {
        return str_replace('%', '%25', $title);
    }
}

if (! \function_exists('convertToHtml')) {
    /**
     * Convert EditorJsData to html.
     */
    function convertToHtml($data)
    {
        $blocks = new EditorBlocksService($data);

        return $blocks->renderHtml();
    }
}

if (! \function_exists('getFirstParagraph')) {
    /**
     * Get First Paragraph from EditorJsData.
     */
    function getFirstParagraph($data)
    {
        $blocks = new EditorBlocksService($data);
        $p = $blocks->renderFirstParagraph();
        $string = substr($p, 0, strpos($p, '</p>') + 4);
        $string = strip_tags($string);
        if (strlen($string) > 500) {
            // truncate string
            $stringCut = substr($string, 0, 500);
            $endPoint = strrpos($stringCut, ' ');

            //if the string doesn't contain any space then it will cut without word basis.
            $string = ($endPoint ? substr($stringCut, 0, $endPoint) : substr($stringCut, 0)).' ...';
        }

        return $string;
    }
}

if (! \function_exists('in_multidimensional_array')) {
    function in_multidimensional_array($value, $array, $strict = false)
    {
        foreach ($array as $item) {
            if (($strict ? $item === $value : $item == $value) || (is_array($item) && in_multidimensional_array($value, $item))) {
                return true;
            }
        }

        return false;
    }
}

if (! \function_exists('getAppURL')) {
    /** Get URL of Website */
    function getAppURL()
    {
        $url = '';
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
            $url .= 'https://';
        } else {
            $url .= 'http://';
        }
        $url .= $_SERVER['HTTP_HOST'];

        return $url;
    }
}

if (! \function_exists('smart_wordwrap')) {
    function smart_wordwrap($string, $width = 75, $break = "\n")
    {
        // split on problem words over the line length
        $pattern = sprintf('/([^ ]{%d,})/', $width);
        $output = '';
        $words = preg_split($pattern, $string, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);

        foreach ($words as $word) {
            if (false !== strpos($word, ' ')) {
                // normal behaviour, rebuild the string
                $output .= $word;
            } else {
                // work out how many characters would be on the current line
                $wrapped = explode($break, wordwrap($output, $width, $break));
                $count = $width - (strlen(end($wrapped)) % $width);

                // fill the current line and add a break
                $output .= substr($word, 0, $count).$break;

                // wrap any remaining characters from the problem word
                $output .= wordwrap(substr($word, $count), $width, $break, true);
            }
        }

        // wrap the final output
        return wordwrap($output, $width, $break);
    }
}

if (! \function_exists('nl2p')) {
    function nl2p($text)
    {
        $text = preg_replace('/\n/', '</p><p>', $text);

        return '<p>'.$text.'</p>';
    }
}

if (! \function_exists('find_problem_word')) {
    function find_problem_word($string)
    {
        function reduce($v, $p)
        {
            return strlen($v) > strlen($p) ? $v : $p;
        }
        // Find problem word
        $problem_word = array_reduce(str_word_count($string, 1), 'reduce');

        if (strlen($problem_word) >= 70) {
            return;
        }

        return $string;
    }
}

if (! function_exists('number_short_format')) {
    function number_short_format(int $n, $precision = 1): string
    {
        switch ($n) {
            case $n < 999:
                // 0 - 999
                $n_format = number_format($n, $precision);
                $suffix = '';
                break;
            case $n < 900000:
                // 0.9k-850k
                $n_format = number_format($n * 0.001, $precision);
                $suffix = 'K';
                break;
            case $n < 900000000:
                // 0.9m-850m
                $n_format = number_format($n * 0.000001, $precision);
                $suffix = 'M';
                break;
            case $n < 900000000000:
                // 0.9b-850b
                $n_format = number_format($n * 0.000000001, $precision);
                $suffix = 'B';
                break;
        }

        if ($precision > 0) {
            $dotzero = '.'.str_repeat('0', $precision);
            $n_format = str_replace($dotzero, '', $n_format);
        }

        return $n_format.$suffix;
    }
}

if (! function_exists('remove_special_chars')) {
    function remove_special_chars($str): string
    {
        // Using preg_replace() function to replace the word
        $res = preg_replace('/[^a-zA-Z0-9_ -]/s', ' ', $str);

        // Returning the result
        return $res;
    }
}

if (! function_exists('toast')) {
    function toast(ToastType $type, string $message, ?RedirectResponse $response = null)
    {
        $toasts = session()->get('toasts', []);
        $toasts[] = [
            'id' => Str::uuid(),
            'type' => $type->value,
            'message' => $message,
        ];
        if ($response) {
            return $response->with('toasts', $toasts);
        } else {
            session()->flash('toasts', $toasts);
        }
    }
}

if (! function_exists('toast_success')) {
    function toast_success(string $message)
    {
        return toast(ToastType::SUCCESS, $message);
    }
}

if (! function_exists('toast_warning')) {
    function toast_warning(string $message)
    {
        return toast(ToastType::WARNING, $message);
    }
}

if (! function_exists('toast_error')) {
    function toast_error(string $message)
    {
        return toast(ToastType::ERROR, $message);
    }
}

if (! function_exists('hash_encode')) {
    function hash_encode($id, $length = 12)
    {
        $hashids = new Hashids('', $length);

        return $hashids->encode($id);
    }
}

if (! function_exists('hash_decode')) {
    function hash_decode($id, $length = 12)
    {
        $hashids = new Hashids('', $length);

        return $hashids->decode($id);
    }
}

if (! function_exists('isValidUuid')) {
    function isValidUuid(mixed $uuid): bool
    {
        return is_string($uuid) && preg_match('/^[a-f\d]{8}(-[a-f\d]{4}){4}[a-f\d]{8}$/i', $uuid);
    }
}

/**
 * Validate a license/purchase key against the database.
 * During initial installation (no database yet), accepts any valid-format key.
 * Once installed, validates against the license_keys table using SHA-256 hashing.
 */
function isPurchaseCode(string $purchaseCode): bool
{
    // During initial installation: no database yet — bootstrap mode
    if (! isAppInstalled()) {
        return true;
    }

    // Normal operation: validate against license_keys table
    return \App\Models\LicenseKey::isValid($purchaseCode);
}

/**
 * Activate a license key for the current installation domain.
 * During initial installation, stores the key to be hashed later.
 * Returns ['buyer' => true] on success, or ['buyer' => false, 'message' => ...] on failure.
 */
function activatePurchaseCode(string $purchaseCode): array
{
    // During initial installation: accept and store the key
    if (! isAppInstalled()) {
        session()->put('bootstrap_license_key', $purchaseCode);

        return ['buyer' => true, 'activated_at' => now()->toDateTimeString()];
    }

    // Normal operation: validate and activate against database
    $domain = request()->getHost();
    $result = \App\Models\LicenseKey::activate($purchaseCode, $domain);

    if ($result['success']) {
        return ['buyer' => true, 'activated_at' => now()->toDateTimeString()];
    }

    return ['buyer' => false, 'message' => $result['message'] ?? 'Activation failed'];
}
