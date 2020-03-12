<?php

require_once __DIR__."/../../../core/Forum/models/ForumEntry.php";

class TranslatorController extends PluginController
{
    public function text_action()
    {
        $language = explode("_", $_SESSION['_language']);
        $language = strtoupper($language[0]);

        $output = [];

        switch (Request::get("item_type")) {
            case "news":
                $news = StudipNews::find(Request::option("id"));
                if (!$news->havePermission("view")) {
                    throw new AccessDeniedException();
                }
                $text = $news['body'];
                break;
            case "message":
                $message = Message::find(Request::option("id"));
                //TODO check if I am able to read this
                $text = $message['message'];
                break;
            case "wikipage":
                list($course_id, $keyword) = explode("_", Request::option("id"));
                $page = WikiPage::findLatestPage($course_id, $keyword);
                $text = $page['body'];
                break;
            case "forumentry":
                $data = ForumEntry::getEntry(Request::option("id"));
                $text = $data['content'];
                break;
        }

        //check if we have this in cache already:
        $cache_hash = "BABELFISH_TEXT_" . md5($language . "__" . $text);
        $cache = StudipCacheFactory::getCache();
        $cached_text = $cache->read($cache_hash);
        if ($cached_text) {
            return formatReady($cached_text);
        }

        $auth_key = Config::get()->BABELFISH_AUTHKEY;

        if (Config::get()->BABELFISH_SERVICE === "DEEPL") {
            $response = file_get_contents("https://api.deepl.com/v2/translate?auth_key=" . urlencode($auth_key) . "&text=" . urlencode($text) . "&target_lang=" . $language);
            if ($response) {
                $response = json_decode($response, true);
                $text = $response['text'];
            }
        }
        if (Config::get()->BABELFISH_SERVICE === "GOOGLE") {
            $request = [
                'q' => $text,
                'target' => strtolower($language),
                'format' => "text"
            ];
        }
        if (Config::get()->BABELFISH_SERVICE === "BING") {
            $url = "https://api-eur.cognitive.microsofttranslator.com/translate?api-version=3.0&textType=html&to=".strtolower($language);

            $payload = [['Text' => $text, 'to' => strtolower($language)]];

            $header = array();
            $header[] = "Ocp-Apim-Subscription-Key: ".$auth_key;
            $r = curl_init();
            curl_setopt($r, CURLOPT_URL, $url);
            curl_setopt($r, CURLOPT_POST, 1);
            curl_setopt($r, CURLOPT_HTTPHEADER, $header);
            curl_setopt($r, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($r, CURLOPT_SSL_VERIFYPEER, true);
            curl_setopt($r, CURLOPT_SSL_VERIFYHOST, true);
            curl_setopt($r, CURLOPT_POSTFIELDS, json_encode($payload));
            $result = curl_exec($r);
            curl_close($r);

            $result = json_decode($result, true);

            if (!$result['error']) {
                $text = $result[0]['translations'][0]['text'];
            }
        }
        if (Config::get()->BABELFISH_SERVICE === "TEST") {
            $text .= "\n\n(This is a test-output by Rasmus.)";
        }

        $cache->write($cache_hash, $text);

        $output['html'] = formatReady($text);

        $this->render_json($output);
    }
}
