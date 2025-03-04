<?php

namespace AppFree\Console\Commands;

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use Illuminate\Console\Command;

class MLogin extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:mlogin';
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
        // https://login.muenchen.de/ui/account/login?service=go-prod
        /*
         * For this case just need to extract the sitekey and send it to our API with full page URL.
         * Then just place the token into cf-turnstile-response and g-recaptcha-response
         */

        //
        //        source command-00

        // Alles löschen

        //source command-01

        // curl -v -b cookie-jar-00.txt -c cookie-jar-00.txt -L -H\
        // 'upgrade-insecure-requests: 1' -H\
        // 'user-agent: Mozilla/5.0 (Linux; Android 12; Nexus 4 Build/SQ1D.220205.004; wv) AppleWebKit/537.36 (KHTML, like Gecko) Vers
        //ion/4.0 Chrome/91.0.4472.114 Mobile Safari/537.36' -H\
        // 'accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signe
        //d-exchange;v=b3;q=0.9' -H\
        // 'x-requested-with: org.chromium.webview_shell' -H\
        // 'sec-fetch-site: none' -H\
        // 'sec-fetch-mode: navigate' -H\
        // 'sec-fetch-user: ?1' -H\
        // 'sec-fetch-dest: document' --compressed -H\
        // 'accept-language: en-US,en;q=0.9' 'https://login.muenchen.de/auth/oauth2/realms/root/realms/customers/authorize?client_id=g
        //o-prod&redirect_uri=https%3A%2F%2Fmvgo-gateway.app.mvg.de%2Foauth%2Fredirect&response_type=code&state=App_zyNudkPWzabnOi2XGi
        //8Fsg&code_challenge=b9BlK9_kTgBKxtTe5a9vmVQfwDRXilo6JvBxfP5TY6I&code_challenge_method=S256&locale=de_DE&login_action=widen_s
        //cope'

        //source command-01-03
        // xsrf token sichern
        // NEU: turnstile lösen

        // curl
        // -H 'upgrade-insecure-requests: 1'
        // -H 'user-agent: Mozilla/5.0 (Linux; Android 12; Nexus 4 Build/SQ1D.220205.004; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/91.0.4472.114 Safari/537.36'
        // -H 'accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9'
        // -H 'x-requested-with: org.chromium.webview_shell'
        // -H 'sec-fetch-site: none'
        // -H 'sec-fetch-mode: navigate'
        // -H 'sec-fetch-user: ?1'
        // -H 'sec-fetch-dest: document'
        // --compressed
        // -H 'accept-language: en-US,en;q=0.9'
        // -H 'cookie: mlogin=twbsMq-8IKER3NxvKSkF37d5q10.*AAJTSQACMDEAAlNLABxoK1dnYm5sRVRid3JxeStLSjlHTzV1dzVMTTg9AAR0eXBlAANDVFMAAlMxAAA.*'
        // -H 'cookie: NSC_MC_mphjodjbn.jousb.txn.ef=7ce2a3d95cdf4704ace91852c6c2769387b71a97ad9fde380da19a5291a4f4563b1fde25'
        // -H 'cookie: XSRF-TOKEN=7ffd0799-1836-427d-91c9-d38f92996939'
        // 'https://login.muenchen.de/auth/oauth2/realms/root/realms/customers/authorize?client_id=go-prod&redirect_uri=https%3A%2F%2Fmvgo-gateway.web.azrapp.swm.de%2Foauth%2Fredirect&response_type=code&state=App_Qij6i9-FeeGiNIdZeouOaw&code_challenge=sYtD5Bz3lJsWyCK8Lyn8TL4AGC1iX6faYr2Ag6B3YaY&code_challenge_method=S256&locale=de_DE&login_action=widen_scope'
        // curl_setopt($ch, CURLOPT_VERBOSE, true);
        $client = new Client();
//        $now = new DateTime('now');
//        $filename = $now->format('Y-m-d_H:i:s');
        $jar = new CookieJar();


        $options = [
            'debug' => true,
            'proxy' => '127.0.0.1:6060',
            'verify' => false,
            'allow_redirects' => true,
            'cookies' => $jar,
            'headers' => [
                'upgrade-insecure-requests' => '1',
                'accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9',
                'user-agent' => 'Mozilla/5.0 (Linux; Android 12; Nexus 4 Build/SQ1D.220205.004; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/91.0.4472.114 Safari/537.36',
                'x-requested-with:' => 'org.chromium.webview_shell',
                'sec-fetch-site' => 'none',
                'sec-fetch-mode' => 'navigate',
                'sec-fetch-user' => '?1',
                'sec-fetch-dest' => 'document',

            ]

        ];

        $url = 'https://login.muenchen.de/auth/oauth2/realms/root/realms/customers/authorize?client_id=go-prod&redirect_uri=https%3A%2F%2Fmvgo-gateway.web.azrapp.swm.de%2Foauth%2Fredirect&response_type=code&state=App_Qij6i9-FeeGiNIdZeouOaw&code_challenge=sYtD5Bz3lJsWyCK8Lyn8TL4AGC1iX6faYr2Ag6B3YaY&code_challenge_method=S256&locale=de_DE&login_action=widen_scope';
        $result = $client->request('GET', $url, $options);
        $result->getBody()->getContents();
//
//        $captchaReq1 = [
//            "clientKey" => "bd768a319499f6dc93dc83493630e58c",
//            "task" => [
//                "type" => "TurnstileTaskProxyless",
//                "websiteURL" => "",
//                "websiteKey" => $websiteKey
//            ]
//        ];


        //source command-02

        // curl --trace command02-trace -b cookie-jar-00.txt -c cookie-jar-00.txt   -o redirectUrl.json -H\
        // 'accept: application/json, text/plain, */*' -H\
        // "x-xsrf-token: $xsrf" -H\
        // 'user-agent: Mozilla/5.0 (Linux; Android 12; Nexus 4 Build/SQ1D.220205.004; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/91.0.4472.114 Mobile Safari/537.36' -H\
        // 'content-type: multipart/form-data; boundary=----WebKitFormBoundaryZ6PCNbiAXfqBAIfV' -H\
        // 'origin: https://login.muenchen.de' -H\
        // 'x-requested-with: org.chromium.webview_shell' -H\
        // 'sec-fetch-site: same-origin' -H\
        // 'sec-fetch-mode: cors' -H\
        // 'sec-fetch-dest: empty' --compressed -H\
        // 'accept-language: en-US,en;q=0.9' \
        //  -X POST 'https://login.muenchen.de/login?sso=true&locale=en' --data-binary $'------WebKitFormBoundaryZ6PCNbiAXfqBAIfV\r\nContent-Disposition: form-data; name="username"\r\n\r\nlpi@mytum.de\r\n------WebKitFormBoundaryZ6PCNbiAXfqBAIfV\r\nContent-Disposition: form-data; name="password"\r\n\r\npillepalle1A\r\n------WebKitFormBoundaryZ6PCNbiAXfqBAIfV--\r\n'

        // NEU in urlencoded form:
        //        cf-turnstile-response: 0.dwrOo1bUUWTbQWpgo3r4l215OwjpTNe1Pzd27aN8nNFrGK9L-pi1Go7K3XamGEfcIg4ctvyrlq_RWdSXbgoLJTooez34lXFg1APbAhwCfYy2hd1SwnxowgEaHX1q2LPjWz6szshqh1uLOvtTABS9Ssc
        //WwRVz_80jeorE8nMlRWDsRf0ZWid6LbrUFBYa9KMxZFoFIINjWBH0ku1o35nu5vzpVfjApUrNDyZI_1HF1tu9JZKfGVcjJ2vWhI9LHKO2ChihDB-LCGM6N4K92NM-WjotiUsZV3-ok_wXQL8ExXqaxmW-t0yLdRxv2j6kmxZ4xkeenxx
        //JJpQz11AQge4gBtwgJhogXageALXVSsUdB1c5NcQPIU78YqkGcLC4SUFDxq2Hqtu3PPn3Ldo9994rMbNn5-hN0g7QZnx_G1WP9n2MZYNaIAKQ9jh32AZNX1oCxBnWZhfM9FASW_jynEZPBDkqCzfktV8ywkXmIOUtTh0kp6vEefgRkQW
        //58k8XAK_lplCjW3hR46QfK2YaPmFWRLakB23_3Wg3CPhUL5bBOo2LkCiCavwKslX8AXrU1DFBYz2oclgC_6TRRGQP9ViCxcRJC-2LYaWnbl6inj8Vdh74sucb09sbmw3HgtYH8MCLc2YM_zIKl5moz_ouU-tbpMltxvD2vHih7n2VQ09
        //K4Sh3gHRqu2aQrx4E2GeNIblPPE-YVPpUgbAx5w7k_AMP0xsMsfHOCg10UPffeer6uiTKcygsBSJPtDHQKQycBlKTV8flSypBn0yudcmd-7JTRHi-4FLGOHAFcPIFpDywIIU_NY6B9n_uPXdvJX7-mWxn6ckBDtjCeV_F3e5zmP8ygWl
        //Wk8BQcmoKbLEIA9oKEAQ9LjuZciyRj1LOGYi4wG9w8T55jHDhGmJix3oeuE1um9HWRVA-1ptaPN138Tg63Eg.rMvvCX1c6haithM8UckEbQ.3da74d23fe302b922c8637658c068969d420d9ceef4867e41aa0aba96b700157
        //_csrf:                 7ffd0799-1836-427d-91c9-d38f92996939


        //source command-02-01
        //source command-03
        //source command-03-01
        //source command-04
        //source command-04-01


    }
}
