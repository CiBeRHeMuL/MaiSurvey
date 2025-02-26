<?php

require_once 'vendor/autoload.php';

use AndrewGos\ClassBuilder\ClassBuilder;
use Symfony\Component\Uid\Uuid;

$builder = new ClassBuilder();

$uuid = $builder->build(Uuid::class, ['uuid' => '1efdb494-40e6-635c-8ac5-8f4fd83c4208']);

echo $uuid->toRfc4122();
