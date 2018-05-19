<?php
class image {
    public static function uploadImg($formname,$query,$params){
        $image = base64_encode(file_get_contents($_FILES[$formname]['tmp_name']));
        $imgUrl = 'https://api.imgur.com/3/image';
        $options = array('http'=>array(
            'method'=>"POST",
            'header'=>"Authorization: Bearer e8dea8e7d7d5e63ce6a4eff78276ff52a1f9abd3\n".
                "Content-Type: application/x-www-form-urlencoded",
            'content'=>$image
        ));
        if ($_FILES[$formname]['size'] > 10240000){
            die('Image file is too big!, must be 10mb or less!');
        }
        $context = stream_context_create($options);
        $response =  file_get_contents($imgUrl, false, $context);
        $response = json_decode($response);
        //echo '<pre>';print_r($response);echo '</pre>';
        $preparams = array($formname=>$response->data->link);
        $params = $params + $preparams;
        DB::query($query,$params);
    }
}