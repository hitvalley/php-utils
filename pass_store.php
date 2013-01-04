#!/usr/bin/php

<?php

const BLOWFISH_ROUNDS = 11;

$pass = $argv[1];

function hashPassword($pass)
{
  // Need to use intermediate variable, used twice
  $nonce = substr(hash('whirlpool', dechex(mt_rand())), 0, 22);
  // Using BLOWFISH, store nonce after password, avoid using additional column in DB to store nonce
  return crypt($pass, '$2a$'.BLOWFISH_ROUNDS.'$'.$nonce.'$').$nonce;
}

function checkPassword($pass, $hashedPass)
{
  return crypt($pass, '$2a$'.BLOWFISH_ROUNDS.'$'.substr($hashedPass, -22).'$') ===
         substr($hashedPass, 0, -22);
}

if (checkPassword($pass, hashPassword($pass))) {
  echo 'PASSWORDS MATCH'.PHP_EOL;
}
else {
  echo 'PASSWORDS DO NOT MATCH'.PHP_EOL;
}
