<?php

/**
 * @file
 * This file is used to generate json files and the directory structure for
 *   GitHub pages.
 */

const DOMAIN = 'cms.blog.localhost';
const URL = 'http://'.DOMAIN;

define(
  'JSON_API_REGEX',
  '/(http:\\\\\/\\\\\/'.preg_quote(
    DOMAIN
  ).'\\\\\/jsonapi\\\\\/[a-zA-Z\=\%\d\-\_\?\\\\\/]+)/'
);
define(
  'JSON_API_SCHEMA_REGEX',
  '/(http:\\\\\/\\\\\/'.preg_quote(
    DOMAIN
  ).'\\\\\/jsonapi\\\\\/[a-zA-Z\=\%\d\-\_\?\\\\\/]+schema)/'
);

$exists = [];

generate(URL.'/jsonapi', JSON_API_REGEX, $exists);
generate(
  URL.'/jsonapi/schema',
  JSON_API_SCHEMA_REGEX,
  $exists
);

function generate($url, $pattern, &$exists) {
  if (!in_array($url, $exists)) {
    create_file($url);
  }

  $json = get_json($url);

  preg_match_all(
    pattern: $pattern,
    subject: $json,
    matches: $matches
  );

  foreach ($matches[1] as $matched_url) {
    if (in_array($matched_url, $exists)) {
      continue;
    }

    create_file($matched_url);

    $exists[] = $matched_url;

    generate(parse_json_url($matched_url), $pattern, $exists);
  }
}

function parse_json_url($url) {
  return str_replace('\/', '/', $url);
}

function get_directory($url) {
  $parsed_url = parse_url(parse_json_url($url));
  $directory = preg_replace('/^\/jsonapi(\/|)/', '', $parsed_url['path']);

  return strlen($directory) == 0 ? '/' : '/'.$directory.'/';
}

function get_json($url) {
  $json_url = parse_json_url($url);

  $context = stream_context_create([
    'http' => [
      'method' => 'GET',
      'header' => "Cookie: SESS01e6e4c658c228c30348ebfd8449ebc5=Gelo70LeBgBHi8zc-G2bCsmia-glNU1rs6zWskwOmmKzHlND",
      'ignore_errors' => TRUE,
    ],
  ]);

  return file_get_contents($json_url, FALSE, $context);
}

function get_contents($url) {
  $contents = get_json($url);
  $contents = str_replace(
    DOMAIN,
    'isobar-playground.github.io',
    $contents
  );
  $contents = str_replace('http:', 'https:', $contents);

  return json_encode(json_decode($contents, TRUE), JSON_PRETTY_PRINT).PHP_EOL;
}

function create_file($url) {
  $directory = getcwd().''.get_directory($url);

  if (!is_dir($directory)) {
    @mkdir(directory: $directory, recursive: TRUE);
  }

  $file_name = $directory.'index.json';
  file_put_contents($file_name, get_contents($url));
}