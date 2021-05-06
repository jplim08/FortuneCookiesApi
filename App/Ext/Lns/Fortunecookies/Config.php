<?php
return [
  'vendor' => 'Lns',
  'module' => 'Fortunecookies',
  'version' => '1.0.1',
  'controllers' =>
  [
    'api_fortune_random' => 'Lns\\Fortunecookies\\Api\\Fortune\\Action\\GetRandom',
    'api_fortune_add' => 'Lns\\Fortunecookies\\Api\\Fortune\\Action\\Add',
    'api_fortune_update' => 'Lns\\Fortunecookies\\Api\\Fortune\\Action\\Update',

  ]
];
