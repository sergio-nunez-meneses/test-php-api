<?php

class TokenModel
{

  public function get_keys($url)
  {
    $keys = json_decode(file_get_contents(json_decode(file_get_contents($url, false, stream_context_create([
      'http'=>[
        'method'=>'GET',
        // 'header'=>''
      ]
    ])), 1)['jwks_uri'], false, stream_context_create([
      'http'=>[
        'method'=>'GET',
        // 'header'=>''
      ]
    ])), 1)['keys'];

    return $keys;
  }
}
