<?php

class InitPlugin extends Migration {

    public function up() {
        Config::get()->create("BABELFISH_SERVICE", array(
            'value' => "DEEPL",
            'type' => "string",
            'range' => "global",
            'section' => "BABELFISH",
            'description' => "Possible services: DEEPL"
        ));
        Config::get()->create("BABELFISH_AUTHKEY", array(
            'value' => "",
            'type' => "string",
            'range' => "global",
            'section' => "BABELFISH",
            'description' => "The auth-key for accessing the service."
        ));
    }

    public function down() {
        Config::get()->delete("BABELFISH_SERVICE");
        Config::get()->delete("BABELFISH_AUTHKEY");
    }

}
