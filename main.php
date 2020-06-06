<?php
error_reporting(0); #E_ALL
ini_set("display_errors", 0);

date_default_timezone_set("Europe/Moscow");

include_once ('scripts_php/links.php');
include_once ('scripts_php/class_translit.php');
include_once ('scripts_php/class_mysql.php');
include_once ('scripts_php/class_article.php');

//echo "<pre>";
//print_r($_SERVER);
//echo "</pre>";

//создание объктов
$Article = new Article;

//проверка прав администратора (возможность добавления редактирования статей)
$Article->check_for_admin();

//вычисляется категория и статья для поиска в б/д
$category_array       = $Article->get_chosen_category_code();
$category_code        = $category_array['category_id'];
$category_keywords    = $category_array['category_keywords'];
$category_name        = $category_array['category_name'];
$category_link        = $category_array['category_link'];
$category_image       = "http://www.dietologia.club/pictures/logo.jpg";
$category_short_descr = "Хотите похудеть и понимаете, что похудение - это всегда комплексный и грамотный процесс, поскольку придется менять свои привычки и чем-то жертвовать? Но это не так - у нас на сайте есть методики, которые сделают этот процесс менее заметным и более приятным! Будьте здоровы и красивы!";

$article_array       = $Article->get_chosen_article_code();
$article_code        = $article_array['article_id'];
$article_keywords    = $article_array['article_keywords'];
$article_name        = $article_array['article_name'];
$article_image       = $article_array['article_picture'];
$article_short_descr = $article_array['article_short_descr'];

#проверка - категория или статья
if (isset($article_keywords) && !empty($article_keywords)){
    #статья
    $keywords          = $article_keywords;
    $title             = "Dиетология :: " . $article_name;
    $show_square_ad    = true;
    $show_image        = "http://www.dietologia.club/pictures/200/" . $article_image;
    $short_description = $article_short_descr;
} else {
    #категория
    $keywords          = $category_keywords;
    $title             = "Dиетология :: " . $category_name;
    $show_square_ad    = false;
    $show_image        = $category_image;
    $short_description = $category_short_descr;
}

//создание перечня категория для главного окна ввода статьи
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta content="text/html" charset="utf-8">
    <?php if ($keywords != "") echo '<meta name="keywords" content="' . $keywords . '">'; ?>

    <meta name="description" content="<?php echo $short_description; ?>" />
    <meta property="og:description" content="<?php echo $short_description; ?>"/>
    <meta property="og:image" content="<?php echo $show_image; ?>"/>
    <meta name="author" content="ZiLo Ltd.">
    <meta name="robots" content="index,follow">    
    <title><?php echo $title; ?></title>
    <link rel="stylesheet" type="text/css" href="css/layout.css">
    <link rel="stylesheet" type="text/css" href="css/article.css">
    <?php
    if ($Article->admin) {
        echo '<script type="text/javascript" src="scripts/class_article.js"></script>
              <link rel="stylesheet" type="text/css" href="css/class_article.css">
              <script type="text/javascript" src="scripts/ajax.js"></script>';
    }
    ?>
    <!-- VKontakte Like Code -->
    <script type="text/javascript" src="//vk.com/js/api/openapi.js?116"></script>
    <script type="text/javascript">VK.init({apiId: 4405834, onlyWidgets: true});</script>
    <!-- VKontakte Like Code -->
    <?php 
    if (!$Article->admin) {
        echo '
        <!-- Yandex.Metrika counter --><script type="text/javascript">(function (d, w, c) { (w[c] = w[c] || []).push(function() { try { w.yaCounter28084826 = new Ya.Metrika({id:28084826, webvisor:true, clickmap:true, trackLinks:true, accurateTrackBounce:true}); } catch(e) { } }); var n = d.getElementsByTagName("script")[0], s = d.createElement("script"), f = function () { n.parentNode.insertBefore(s, n); }; s.type = "text/javascript"; s.async = true; s.src = (d.location.protocol == "https:" ? "https:" : "http:") + "//mc.yandex.ru/metrika/watch.js"; if (w.opera == "[object Opera]") { d.addEventListener("DOMContentLoaded", f, false); } else { f(); } })(document, window, "yandex_metrika_callbacks");</script><noscript><div><img src="//mc.yandex.ru/watch/28084826" style="position:absolute; left:-9999px;" alt="" /></div></noscript>
        <!-- /Yandex.Metrika counter -->';
        echo "
        <!-- /Google Analytics counter -->
        <script>
          (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
          (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
          m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
          })(window,document,'script','//www.google-analytics.com/analytics.js','ga');
          ga('create', 'UA-58947718-1', 'auto');
          ga('send', 'pageview');
        </script>
        <!-- /Google Analytics counter -->
        ";
    }
    ?>
</head>

<body onload="sticky_header();">
    <header id="header">
        <div id="header_picture"></div>
        <div id="header_cont">
            <div id="header_name"><a class="main_header_link" href="<?php echo $server . $main_page; ?>">Dиетология.club</a></div>
            <ul class="navigation">
                <li class="nav">
                    <a href="<?php echo $server . $main_page; ?>">На главную</a>
                </li>
            </ul>
            <div id="nav_path">
                <a class="main_header_link" href="<?php echo $server . $main_page; ?>">Главная страница</a>
                <?php
                if (isset($category_array['category_name']) 
                    & !empty($category_array['category_name'])
                    & $category_array['category_id'] != '00'){
                        echo ' >>> <a class="main_header_link" href="';
                        echo $server . $main_page . $category_link .'/page-1/">' . $category_array['category_name'] . '</a>';
                    }
                ?>
            </div>
        </div>
    </header>
    <div id="global_wrapper">
    <?php 
        echo $Article->show_page($category_code, $article_code); 
    ?>
        <aside class="aside">
            <h2 class="categories_header">Разделы сайта</h2>
            <?php echo $Article->get_category(); 
            if ($show_square_ad) {
            echo '
            <div id="square_ad">
                <script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
                <!-- dietologia - box -->
                <ins class="adsbygoogle"
                     style="display:inline-block;width:250px;height:250px"
                     data-ad-client="ca-pub-6955574409419903"
                     data-ad-slot="2093238287"></ins>
                <script>
                (adsbygoogle = window.adsbygoogle || []).push({});
                </script>
            </div>';
            }
            ?>
        </aside>
    </div>
    <footer class="footer">ZiLo Ltd. 2015</footer>
    <script>
    function sticky_header(){
        var divHeader = document.querySelector("div#header_cont"),
        divWrapper = document.querySelector("div#global_wrapper"),
        HTMLtop = document.documentElement.getBoundingClientRect().top,
        t0 = divHeader.getBoundingClientRect().top - HTMLtop,
        divGlobalWrapper = document.querySelector("div#global_wrapper").getBoundingClientRect().bottom - HTMLtop - divHeader.offsetHeight,
        footer = document.querySelector("footer"),
        header_height=divHeader.offsetHeight;

        function scrolling() {
            if (window.pageYOffset > divGlobalWrapper) {
                divHeader.className = 'stopped';
                divHeader.style.top = divGlobalWrapper - t0 + 'px';                
            } else {
                divHeader.className = (t0 < window.pageYOffset ? 'sticked' : '');
                divHeader.style.top = '0';
            }
            if (divHeader.classList.contains("sticked")) {
                divWrapper.style.top = header_height + "px";
                footer.style.top = header_height + "px";
            } else {
                divWrapper.style.top = "0";
                footer.style.top = "0";
            }
        }
        window.addEventListener('scroll', scrolling, false);
    }
    <?php
    $main_window_code = null;
    if ($Article->admin) {
        $main_window_code = '<div id="main_window"><table id="tbl" border="0" cellpadding="0" cellspacing="5"><tr><td width="20%">Категория статьи</td><td>'.$Article->get_selected_categories().'</td></tr><tr><td colspan="2">Название статьи</td></tr><tr><td colspan="2"><input type="text" name="article_name" id="article_name" value=""></td></tr><tr><td>Добавить картинку</td><td><input id="my_id_file" type="file"/></td></tr><tr><td colspan="2">Текст статьи</td></tr><tr><td colspan="2"><textarea name="article_content" id="article_content"></textarea></td></tr><tr><td colspan="2">Ключевые слова (через запятую):</td></tr><tr><td colspan="2"><textarea name="article_keywords" id="article_keywords">Не пишите сюда ничего!</textarea></td></tr><tr><td colspan="2"><input type="button" id="my_send" value="Загрузить"/><input type="button" id="cancel" value="Сбросить"/></td></tr></table><input type="hidden" id="author_id" value="'.$Article->author.'"></div>';
    }?>
    main_window_code = <?php echo "'".$main_window_code."'";?>;
    </script>
</body>
</html>
