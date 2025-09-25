<?php

function getNotificationIcon($title, $body)
{
  $text = strtolower($title . ' ' . $body);

  if (str_contains($text, 'alert') || str_contains($text, 'error')) {
    return '/assets/img/alert.png';
  } elseif (str_contains($text, 'update') || str_contains($text, 'system')) {
    return '/assets/img/update.png';
  } elseif (str_contains($text, 'reminder') || str_contains($text, 'schedule')) {
    return '/assets/img/reminder.png';
  } elseif (str_contains($text, 'message') || str_contains($text, 'reply')) {
    return '/assets/img/message-notify.png';
  } elseif (str_contains($text, 'respondent') || str_contains($text, 'feedback')) {
    return '/assets/img/feedback-notify.png';
  } else {
    return '/assets/img/info.png'; // default
  }
}
