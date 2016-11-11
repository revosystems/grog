<?php

Html::macro("configForm", function($type, $model, $field, $showDesc = false, $selectArray = null){

    if      ($type == "text")       $b = Form::text     ($field, $model->$field,        ['placeholder' => trans_choice('admin.'.$field,1) , 'id' => $field]);
    else if ($type == "textarea")   $b = Form::textarea ($field, $model->$field,        ['placeholder' => trans_choice('admin.'.$field,1) , 'id' => $field]);
    else if ($type == "email")      $b = Form::email    ($field, $model->$field,        ['placeholder' => trans_choice('admin.'.$field,1) , 'id' => $field]);
    else if ($type == "date")       $b = Form::date     ($field, $model->$field,        ['placeholder' => trans_choice('admin.'.$field,1) , 'id' => $field]);
    else if ($type == "url")        $b = Form::url      ($field, $model->$field,        ['placeholder' => trans_choice('admin.'.$field,1) , 'id' => $field]);
    else if ($type == "number")     $b = Form::number   ($field, $model->$field,        ['placeholder' => trans_choice('admin.'.$field,1) , 'id' => $field]);
    else if ($type == "color")      {
        $b =  Form::color   ($field, $model->$field,       ['placeholder' => trans_choice('admin.'.$field,1) , 'id' => 'colorpicker']);
        $b .= Form::text    ($field, $model->$field,       ['placeholder' => trans_choice('admin.'.$field,1) , 'id' => $field]);
    }

    else if ($type == "select")     $b = Form::select   ($field, $selectArray, $model->$field, ['id' => $field]);
    else if ($type == "check")      $b = Form::check    ($field, true, $model->$field,  ['id' => $field]);
    else if ($type == "hidden")     {
        $b = Form::text     ($field, $model->$field, ['id'=>$field,'style'=>'display:none']);
        echo $b;
        return;
    }

    echo "<div class='label'>";
    $a = Form::label($field.'Label',  trans_choice('admin.'.str_replace("_id","",$field),1));
    echo $a;
    echo "</div><div class='field''>";
    echo $b;
    if($showDesc){
        echo "<br><p>". trans('admin.'.$field.'Desc') . "</p>";
    }
    echo"</div>";

});

/**
 * Form check that auto adds the hidden false field for when checkbox not checked
 */
Form::macro("check", function($name, $value = 1, $checked = null, $options = array()){
    return Form::hidden($name, 0).Form::checkbox($name, $value, $checked, $options);
});

Html::macro("gravatar", function($email, $size = 30){

    $email = md5(strtolower(trim($email)));

    //Generate Image URL
    $gravatarURL = "https://www.gravatar.com/avatar/";
    $gravatarURL.= $email."?s=".$size."&d=mm";

    echo '<img id = '.$email.''.$size.' class="gravatar" src="'.$gravatarURL.'" width="'.$size.'">';

});

Html::macro('active', function($active){
    echo "<span style='color:black'>";
    if($active)     echo FA::icon('check');
    else            echo FA::icon('times');
    echo "</span>";
});

Html::macro('toggle',function($active){
    if($active)     echo "<span style='color:green'>" . FA::icon('toggle-on')->x2();
    else            echo "<span style='color:gray'>" . FA::icon('toggle-off')->x2();
    echo "</span>";
});

Html::macro('featured', function($active){
    echo "<span style='color:black'>";
    if($active)     echo FA::icon('star');
    else            echo FA::icon('star-o');
    echo "</span>";
});


Html::macro('loadingImage', function(){
    echo "<span class='loadingImage'>";
    echo FA::spin('circle-o-notch');
    echo "</span>";
});

Html::macro('saveButton', function($text = null){
    echo "<button type=\"submit\" class=\"button\" onclick=\"$(this).css('width','+=15');\">";
    echo Html::loadingImage() ." ";
    if($text == null)
        echo Trans('admin.save');
    else{
        echo $text;
    }
    echo "</button>";
});

