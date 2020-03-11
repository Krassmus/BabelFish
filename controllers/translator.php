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
        if (Config::get()->BABELFISH_SERVICE === "TEST") {
            $text .= "\n\n(This is a test-output by Rasmus.)";
        }

        $output['html'] = formatReady($text);

        $this->render_json($output);
    }
}
