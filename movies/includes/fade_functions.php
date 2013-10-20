<?php
  function FadeIn(&$tmpl, $id, $timeout){
    $tmpl->addVar("setTimeout", "COMMAND", "fade = new MyFade(); fade.FadeIn('" . $id . "', 0, 85, 5, 'fade')");
    $tmpl->addVar("setTimeout", "MILLISECONDS", $timeout);
    $tmpl->parseTemplate("setTimeout", "a");
  }

  function FadeInExtra(&$tmpl, $id, $timeout, $start_opacity, $end_opacity){
    $tmpl->addVar("setTimeout", "COMMAND", "fade = new MyFade(); fade.FadeIn('" . $id . "', " . $start_opacity . ", " . $end_opacity . ", 3, 'fade')");
    $tmpl->addVar("setTimeout", "MILLISECONDS", $timeout);
    $tmpl->parseTemplate("setTimeout", "a");
  }
  
  function FadeOut(&$tmpl, $id, $timeout){
    $tmpl->addVar("setTimeout", "COMMAND", "fadeout = new MyFade(); fadeout.FadeOut('" . $id . "', 0, 1, 'fadeout')");
    $tmpl->addVar("setTimeout", "MILLISECONDS", $timeout);
    $tmpl->parseTemplate("setTimeout", "a");
  }
?>