<?php

/**
 * entering data:
 *
 *   [id] => 1
 *   [category] => 14
 *   [content] => какой-то текст
 *   [name] => Название
 *   [author] => 1
 *   [link] => nazvanie
 *   [flag] => add||edit
 *   [picture] => name-140193893847
 *
 * Поля базы данных статей
 *
 *   article_id           int(11)
 *   article_author_id    int(11)
 *   article_name         varchar(256)
 *   article_picture      varchar(256)
 *   article_content      text
 *   article_category     varchar(128)
 *   article_link         varchar(256)
 *   article_date         int(11)
 *   article_reads        int(11)
 *   article_comments     int(11)
 *   article_rating       int(11)
 */

header("Content-type: application/x-www-form-urlencoded; charset=utf-8");
include_once ("class_article.php");

$new_article = new Article;

if($_POST["flag"] == "add"){
	$new_article->add_article($_POST);
} elseif ($_POST["flag"] == "load") {
	$new_article->load_article($_POST);
} elseif ($_POST["flag"] == "edit") {
	$new_article->edit_article($_POST);
}
?>