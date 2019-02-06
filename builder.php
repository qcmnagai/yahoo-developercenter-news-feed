<?php

date_default_timezone_set('Asia/Tokyo');
require __DIR__.'/vendor/autoload.php';

use Goutte\Client;
use FeedWriter\ATOM;

$Feed = new ATOM();
$Feed->setTitle('Yahoo Japan Marketing Solution Developer Center News');
$Feed->setLink('https://biz.marketing.yahoo.co.jp/developercenter/news/');
$Feed->setDate(new DateTime());
$Feed->setSelfLink('https://qcmnagai.github.io/yahoo-developercenter-news-feed/atom.xml');

//Create an empty Item
$client = new Client();
$crawler = $client->request('GET', 'https://biz.marketing.yahoo.co.jp/developercenter/news/');
$articles = $crawler->filter('article.cat');
foreach($articles as $article) {
    $newItem = $Feed->createNewItem();

    $link = $article->childNodes[1]->childNodes[0];
    $title = trim($link->textContent);
    $newItem->setTitle($title);

    $href = $link->attributes[0];
    $newItem->setLink($href->textContent);

    $date = $article->childNodes[3];
    $updatedDatetime = \DateTime::createFromFormat('Y年n月j日H:i', str_replace(' ', '', $date->textContent));
    $newItem->setDate($updatedDatetime);

    $newItem->setDescription($title.'...');
    $newItem->setContent($title.'...');
    $Feed->addItem($newItem);
}

file_put_contents(__DIR__.'/docs/atom.xml', $Feed->generateFeed(), LOCK_EX);
