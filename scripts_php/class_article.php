<?php
include_once ("class_mysql.php");
//include_once ("class_keywords.php");

class Article
{
    
    /*
     Поля базы данных статей
     * article_id           int(11)
     * article_author_id    int(11)
     * article_name         varchar(256)
     * article_picture      varchar(256)
     * article_content      text
     * article_category     varchar(128)
     * article_link         varchar(256)
     * article_date         int(11)
     * article_reads        int(11)
     * article_comments     int(11)
     * article_rating       int(11)
    */
    
    //database block
    private $db_name = "dietologia";
    private $db_article = "articles";
    private $db_category = "category";
    private $db_author = "authors";
    private $sql_string = null;
    
    //pages block
    private $total_pages = 1;
    private $current_page = 1;
    private $articles_per_page = 10;
    private $max_count_big_article = 4;
    
    //Common Data
    public $link = array();
    public $name = array();
    public $readers = array();
    public $picture = array();
    public $content = array();
    public $category = array();
    public $comments = array();
    public $category_name = array();
    public $category_link = array();
    public $symbols_array = array();
    public $payment_array = array();
    public $current_category = array();
    public $category_description = null;
    public $current_category_code = null;
    
    //Admin and Author
    private $author_id = null;
    private $author_price = null;
    public $admin = null;
    public $author = null;
    
    //output HTML
    public $right_side_column = null;
    public $output_page = null;
    public $category_page = null;
#########################end of declaration################################
    
/*######################################################################################################################
                                             make_condition($conditional_array)
######################################################################################################################*/
    private function make_condition($conditional_array) {
        if (count($conditional_array) == 0) return;
        $sql_condition = " where ";
        $conditions_amount = count($conditional_array);
        
        for ($i = 0; $i < $conditions_amount; $i++) {
            $sql_condition.= $conditional_array[$i] . " or ";
        }
        $sql_condition = substr($sql_condition, 0, -4);
        return $sql_condition;
    }
/*######################################################################################################################
                                            make_groupping($groupping_field)
######################################################################################################################*/
    private function make_groupping($groupping_field) {
        if (isset($groupping_field)) {
            $sql_group = " group by  `" . $groupping_field . "`";
            return $sql_group;
        }
    }
/*######################################################################################################################
                                              make_sorting($sorting_field, $sorting_order)
######################################################################################################################*/
    private function make_sorting($sorting_field, $sorting_order) {
        $sql_sort = " order by `" . $sorting_field . "` " . $sorting_order;
        return $sql_sort;
    }
/*######################################################################################################################
                                             get_category_name($category_code, $iterator)
######################################################################################################################*/
    private function get_category_name($category_code, $iterator) {
        $_sql = "select * from `" . $this->db_name . "`.`" . $this->db_category . "`";
        $_sql.= " where `category_id` = '" . $category_code . "'";
        
        /* mysql request */
        $mysql = new mysql\Mysql;
        $mysql->connect();
        $mysql->sql($_sql);
        $values = $mysql->getResult();
        $mysql->disconnect();
        
        /* присваиваем категории*/
        $this->category_name[$iterator] = $values[0]["category_name"];
        $this->category_link[$iterator] = $values[0]["category_link"];
    }
/*######################################################################################################################
                                              get_category_description($_category)
######################################################################################################################*/
    private function get_category_description($_category) {
        $_sql = "select category_description from `" . $this->db_name . "`.`" . $this->db_category . "`";
        $_sql.= " where `category_id` = '" . $_category . "'";
        
        /* mysql request */
        $mysql = new mysql\Mysql;
        $mysql->connect();
        $mysql->sql($_sql);
        $values = $mysql->getResult();
        $mysql->disconnect();
        
        /* присваиваем категории*/
        $this->category_description = $values[0]["category_description"];
    }
/*######################################################################################################################
                                            get_author_data($_author_id)
######################################################################################################################*/
    private function get_author_data($_author_id) {
        $this->sql_string = "select `author_name`,`author_price` from `" . $this->db_name . "`.`" . $this->db_author;
        $this->sql_string.= "` where `author_id` = " . $_author_id . ";";
        
        $mysql = new mysql\Mysql;
        $mysql->connect();
        $mysql->sql($this->sql_string);
        $values = $mysql->getResult();
        $mysql->disconnect();
        
        $this->author = $_author_id;
        $this->author_name = $values[0]['author_name'];
        $this->author_price = $values[0]['author_price'];
    }
/*######################################################################################################################
                                              make_array_data($_value)
######################################################################################################################*/
    private function make_array_data($_value) {
        $values_lenght = count($_value);
        
        for ($i = 0; $i < $values_lenght; $i++) {
            
            /* заполняем массивы с данными */
            $this->id[$i] = $_value[$i]["article_id"];
            $this->link[$i] = $_value[$i]["article_link"];
            $this->name[$i] = $_value[$i]["article_name"];
            $this->readers[$i] = $_value[$i]["article_reads"];
            $this->picture[$i] = $_value[$i]["article_picture"];
            $this->content[$i] = nl2br($_value[$i]["article_content"]);
            $this->current_category[$i] = $_value[$i]["article_category_0"];
            $this->comments[$i] = $_value[$i]["article_comments"];
            $this->created[$i] = date("d.m.Y, G:i", $_value[$i]["article_date"]);
            $this->symbols_array[$i] = $_value[$i]["article_symbols"];
            $this->payment_array[$i] = $_value[$i]["article_price"];
            $this->article_author_id_array[$i] = $_value[$i]["article_author_id"];

            //$current_category = $_value[$i]["article_category"];
            $this->get_category_name($this->current_category[$i], $i);
        }
    }
    
/*######################################################################################################################
                                             get_category_data($category)
######################################################################################################################*/
    private function get_category_data($category) {
        /* запрашиваем категорию целиком */
        $this->admin ? $amount = 100 : $amount = $this->articles_per_page;
        //необходимо для условия LIMIT  в запросе SQL
        $_page_sql = ($this->current_page - 1) * $this->articles_per_page;
        //добавление всяких условий к запросу
        $conditions = array();
        if ($category != "00" && $category != "01") {
            $conditions = array(0 => "`article_category_0` = '" . $category . "'",
                                1 => "`article_category_1` = '" . $category . "'",
                                2 => "`article_category_2` = '" . $category . "'");
        }
        //построение SQL запроса
        $this->sql_string = "select * from `" . $this->db_name . "`.`" . $this->db_article . "`";
        $this->sql_string.= $this->make_condition($conditions);
        $this->sql_string.= $this->make_groupping(null);
        $this->sql_string.= $this->make_sorting("article_date", "desc");
        $_limit = " limit " . $_page_sql . "," . $amount . "";

        //echo $this->sql_string;

        //делаем mysql
        $mysql = new mysql\Mysql;
        $mysql->connect();
        $mysql->sql($this->sql_string . $_limit);
        $val_one = $mysql->getResult();
        $mysql->sql($this->sql_string);
        $val_two = $mysql->getResult();
        $mysql->disconnect();

        //запрашиваем описание категории
        $this->get_category_description($category);
        //формируем данные в объект для выдачи
        $this->make_array_data($val_one);
        //подсчитываем количество страниц
        $this->total_pages = ceil(count($val_two) / $this->articles_per_page);
    }
    
/*######################################################################################################################
                                              get_article_data($article)
######################################################################################################################*/
    private function get_article_data($article) {
        
        /* запрашиваем статью целиком */
        $conditions = array();
        $conditions = array(0 => "`article_id` = '" . $article . "'");
        $this->sql_string = "select * from `" . $this->db_name . "`.`" . $this->db_article . "`";
        $this->sql_string.= $this->make_condition($conditions);
        
        $mysql = new mysql\Mysql;
        $mysql->connect();
        $mysql->sql($this->sql_string);
        $values = $mysql->getResult();
        $mysql->disconnect();
        
        //выясняем автора данного опуса
        //$this->author_id = $values[0]['article_author_id'];
        $this->get_author_data($values[0]['article_author_id']);
        
        //готовим данные в массив к выдаче
        $this->make_array_data($values);
        
        //обновляем SQL строку и данные в базе
        $article_reads = $values[0]['article_reads'] + 1;
        $this->sql_string = "update `" . $this->db_name . "`.`" . $this->db_article . "` ";
        $this->sql_string.= "set article_reads = " . $article_reads . " where article_id = " . $values[0]['article_id'];
        
        $mysql = new mysql\Mysql;
        $mysql->connect();
        $mysql->sql($this->sql_string);
        $mysql->disconnect();
    }
    
/*######################################################################################################################
                                             show_article()
######################################################################################################################*/
    private function show_article() {
        global $server, $main_page;
        
        $this->output_page = '
        <div id="wrapper">
            <div class="main_article">
                <div class="article">
                    <div id="article_cont_big_no_limits_article">
                        <div id="header_box">
                            <div id="top_diet_data_article">
                                <h1 id="diet_name_article">
                                    <a href="' . $server . $main_page . $this->category_link[0] . '/' . $this->link[0] . '.html" class="header_link">
                                        ' . $this->name[0] . '
                                    </a>
                                </h1>
                                <div class="diet_category_article">Опубликовано: ' . $this->created[0] . '</div>
                                <div class="diet_category_article">Авторство: ' . $this->author_name . '</div>
                                <div class="diet_category_article">Прочтено: ' . $this->readers[0] . ' раз(а)</div>
                                <div id="soc_net">
                                    <!-- Vkontakte Like Button -->
                                    <div id="vk_like"></div>
                                    <script type="text/javascript">VK.Widgets.Like("vk_like", {type: "button"});</script>
                                </div>
                            </div>
                        </div>
                        <div id="image_box">
                            <a href="' . $server . $main_page . $this->category_link[0] . '/' . $this->link[0] . '.html" class="standart_link_no_border">
                                <img id="floated_image_article" src="pictures/200/' . $this->picture[0] . '">
                            </a>
                        </div>
                        <div class="adv_cont">
                            <div class="adv_horiz_1">
                                <script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
                                <!-- dietologia - top -->
                                <ins class="adsbygoogle"
                                     style="display:inline-block;width:728px;height:90px"
                                     data-ad-client="ca-pub-6955574409419903"
                                     data-ad-slot="4540599296"></ins>
                                <script>
                                (adsbygoogle = window.adsbygoogle || []).push({});
                                </script>
                            </div>
                        </div>
                        <article id="article_text">' . $this->content[0] . '</article>
                    </div>
                <div class="gradient_line"></div>
                </div>
            </div>
            <div class="three_columns"></div>
            <div class="adv_cont_bottom">
                <div class="adv_horiz_2">
                    <script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
                    <!-- dietologia - bottom -->
                    <ins class="adsbygoogle"
                         style="display:inline-block;width:728px;height:90px"
                         data-ad-client="ca-pub-6955574409419903"
                         data-ad-slot="3383184898"></ins>
                    <script>
                    (adsbygoogle = window.adsbygoogle || []).push({});
                    </script>
                </div>
            </div>
            <!-- Put this div tag to the place, where the Comments block will be -->
            <div id="vk_comments"></div>
            <script type="text/javascript">
                VK.Widgets.Comments("vk_comments", {limit: 10, width: "728", attach: "*"});
            </script>
        </div>
        ';
    }
        
/*######################################################################################################################
                                            build_pages_block()
######################################################################################################################*/
    private function build_pages_block() {
        global $server, $main_page;
        $_button_block = "";
        for ($i = 0; $i < $this->total_pages; ++$i){
            if (($i + 1) == intval($this->current_page)) {$selected = 'id="selected_button"';} else {$selected = null;}

            $_button_block.= '<a href="' . $server . $main_page . $_GET['category'] . '/page-' . ($i + 1);
            $_button_block.= '/"><div '.$selected.' class="page_button">' . ($i + 1) . '</div></a>';
        }
        return $_button_block;
    }
    
/*######################################################################################################################
                                            show_category()
######################################################################################################################*/
    private function show_category() {
        global $server, $main_page;
        $this->output_page = '
        <div id="wrapper">
        ' . $this->category_description . '
            <div class="main_article">';
        
        if ($this->admin) {
            $max_count_big_article = 100;
            $total_records_big_article = count($this->name);
        } else {
            $max_count_big_article = $this->max_count_big_article;
            $total_records_big_article = count($this->name);
        }
        for ($i = 0; $i < min($total_records_big_article, $max_count_big_article); $i++) {
            $this->output_page.= '
                <div class="article">
                    <div class="article_cont_big">
                        <div class="top_diet_data">
                            <h2 class="diet_name">
                                <a href="' . $server . $main_page . $this->category_link[$i] . '/' . $this->link[$i] . '.html" class="header_link">
                                    ' . $this->name[$i] . '
                                </a>
                            </h2>
                            <div class="diet_category"><!--Раздел: 
                                <a href="' . $server . $main_page . $this->category_link[$i] . '/page-1/" class="standart_link">
                                    ' . $this->category_name[$i] . '
                                </a><br>
                                <a href="' . $server . $main_page . $this->category_link[$i] . '/page-1/" class="standart_link">
                                    ' . $this->category_name[$i] . '
                                </a><br>
                                <a href="' . $server . $main_page . $this->category_link[$i] . '/page-1/" class="standart_link">
                                    ' . $this->category_name[$i] . '
                                </a>-->';
            
            if ($this->admin) {
                if ($_GET['user_editor'] == $this->article_author_id_array[$i]){
                    $this->output_page.= '&nbsp;&nbsp;&nbsp;';
                    $this->output_page.= '<span class="symbols">' . $this->symbols_array[$i] . '</span>&nbsp;&nbsp;';
                    $this->output_page.= '<span class="price">' . $this->payment_array[$i] . '</span>';
                } elseif ($_GET['user_editor'] == "107078") {
                    $this->output_page.= '&nbsp;&nbsp;&nbsp;';
                    $this->output_page.= '<span class="symbols">' . $this->symbols_array[$i] . '</span>&nbsp;&nbsp;';
                    $this->output_page.= '<span class="price">' . $this->payment_array[$i] . '</span>&nbsp;&nbsp;';
                    $this->output_page.= '<span class="edit_articles" onclick="edit_article(' . $this->id[$i] . ');">Edit</span>';
                    $this->output_page.= '<input type="hidden" id="author_id" value="' . $_GET['user_editor'] . '"/>';
                }
            }
            
            $this->output_page.= '
                            </div>
                        </div>
                        <a href="' . $server . $main_page . $this->category_link[$i] . '/' . $this->link[$i] . '.html" class="standart_link">
                            <img class="floated_image" src="pictures/150/' . $this->picture[$i] . '">
                        </a>
                        <article>' . $this->content[$i] . '</article>
                    </div>
                    <div class="cont_read_more">
                        <div class="read_more">
                            <a href="' . $server . $main_page . $this->category_link[$i] . '/' . $this->link[$i] . '.html" class="standart_link">
                                <span class="read_more_symbol">»</span> Читать далее...
                            </a>
                        </div>
                        <div class="statistic">Прочитано: ' . $this->readers[$i] . ' раз(а)</div>
                    </div>
                    <div class="gradient_line"></div>
                </div>';

            #place ads block after the second  article if total of them are 4
            if ($i == 1 && min($total_records_big_article, $max_count_big_article) == 4) {
                $this->output_page .= '
                <div class="adv_cont_bottom_2">
                    <div class="adv_horiz_2">
                        <script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
                        <!-- dietologia - middle -->
                        <ins class="adsbygoogle"
                             style="display:inline-block;width:728px;height:90px"
                             data-ad-client="ca-pub-6955574409419903"
                             data-ad-slot="0334639199"></ins>
                        <script>
                        (adsbygoogle = window.adsbygoogle || []).push({});
                        </script>
                    </div>
                </div>
                ';
            }

        }
        $this->output_page.= '
            </div>
            <div class="three_columns">
            ';
        if ($this->admin) {
            $max_count_small_article = 100;
            $total_records_small_article = count($this->name);
        } else {
            $max_count_small_article = 100;
            $total_records_small_article = count($this->name);
        }
        for ($i = min($total_records_big_article, $max_count_big_article); $i < min($total_records_small_article, $max_count_small_article); $i++) {
            $this->output_page.= '   
                <div class="column">
                    <div class="article_cont_small">
                        <h3 class="header_3">
                            <a href="' . $server . $main_page . $this->category_link[$i] . '/' . $this->link[$i] . '.html" class="header_link">
                            ' . $this->name[$i] . '</a>
                        </h3>
                        <a href="' . $server . $main_page . $this->category_link[$i] . '/' . $this->link[$i] . '.html" class="standart_link">
                            <img class="floated_image" src="pictures/100/' . $this->picture[$i] . '"/>
                        </a>
                        <article>' . $this->content[$i] . '</article>
                    </div>
                    <div class="cont_read_more">
                        <div class="read_more">
                            <a href="' . $server . $main_page . $this->category_link[$i] . '/' . $this->link[$i] . '.html" class="standart_link">
                                <span class="read_more_symbol">»</span> Читать далее...
                            </a>
                        </div>
                        <div class="statistic">Прочитано: ' . $this->readers[$i] . ' раз(а)</div>
                    </div>
                </div>
                ';
        }
                
        $this->output_page.= '
            </div>

            <div id="page_buttons">
                <div class="page_button_cont">
                    <div id="left_arrow"></div>
                    <div id="cont_visible_area">
                        <div id="visible_area" style="width:'. 2.8 * $this->total_pages .'em">' . 
                            $this->build_pages_block() . '
                        </div>
                    </div>
                    <div id="right_arrow"></div>
                </div>
            </div>

            <div class="adv_cont_main">
                <div class="adv_horiz_1">
                    <script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
                    <!-- dietologia - top -->
                    <ins class="adsbygoogle"
                         style="display:inline-block;width:728px;height:90px"
                         data-ad-client="ca-pub-6955574409419903"
                         data-ad-slot="4540599296"></ins>
                    <script>
                    (adsbygoogle = window.adsbygoogle || []).push({});
                    </script>
                </div>
            </div>
            <div class="adv_cont_bottom">
                <div class="adv_horiz_2">
                    <script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
                    <!-- dietologia - bottom -->
                    <ins class="adsbygoogle"
                         style="display:inline-block;width:728px;height:90px"
                         data-ad-client="ca-pub-6955574409419903"
                         data-ad-slot="3383184898"></ins>
                    <script>
                    (adsbygoogle = window.adsbygoogle || []).push({});
                    </script>
                </div>
            </div>
        </div>
        ';
    }

/*######################################################################################################################
                                             get_keywords($contents,$symbol=5,$words=50)
######################################################################################################################*/
    private function get_keywords($contents,$symbol=5,$words=50){
        $contents = mb_convert_case($contents, MB_CASE_LOWER, "UTF-8");
        $contents = @preg_replace(array("'<[\/\!]*?[^<>]*?>'si","'([\r\n])[\s]+'si","'&[a-z0-9]{1,6};'si","'( +)'si"),

            array("","\\1 "," "," "),strip_tags($contents));

        $rearray = array("~","!","@","#","$","%","^","&","*","(",")","_","+",
           "`",'"',"№",";",":","?","-","=","|","\"","\\","/",
           "[","]","{","}","'",",",".","<",">","\r\n","\n","\t","«","»",
           "грамм","грамма","штук","штука","штуки");

        $adjectivearray = array("ые","ое","ие","ий","ая","ый","ой","ми","ых","ее","ую","их","ым",
            "как","для","что","или","это","этих",
            "всех","вас","они","оно","еще","когда",
            "где","эта","лишь","уже","вам","нет",
            "если","надо","все","так","его","чем",
            "при","даже","мне","есть","только","очень",
            "сейчас","точно","обычно"
            );

        $contents = @str_replace($rearray," ",$contents);
        $keywordcache = @explode(" ",$contents);
        $rearray = array();

        foreach($keywordcache as $word){
            if(strlen($word)>=$symbol && !is_numeric($word)){
                $adjective = substr($word,-2);
                if(!in_array($adjective,$adjectivearray) && !in_array($word,$adjectivearray)){
                    $rearray[$word] = (array_key_exists($word,$rearray)) ? ($rearray[$word] + 1) : 1;
                }
            }
        }

        @arsort($rearray);
        $keywordcache = @array_slice($rearray,0,$words);
        $keywords = "";

        foreach($keywordcache as $word=>$count){
            $keywords.= ", ".$word;
        }

        return substr($keywords,1);
    }    
/*######################################################################################################################
                                             format_text()     
######################################################################################################################*/
    private function format_text($text) {
        $pattern = array("/[\r\n]/","/##/","/@(.+?)#/","/кг[\.,;)]?\s{0,}?#/","/шт[\.,;)]?\s{0,}?#/","/мл[\.,;)]?\s{0,}?#/","/гр[\.,;)]?\s{0,}?#/","/л[\.,;)]?\s{0,}?#/","/  /","/1 ст\.л\./","/2 ст\.л\./","/3 ст\.л\./","/4 ст\.л\./","/5 ст\.л\./","/6 ст\.л\./","/7 ст\.л\./","/8 ст\.л\./","/9 ст\.л\./","/0 ст\.л\./","/1 ч\.л\./","/2 ч\.л\./","/3 ч\.л\./","/4 ч\.л\./","/5 ч\.л\./","/6 ч\.л\./","/7 ч\.л\./","/8 ч\.л\./","/9 ч\.л\./","/0 ч\.л\./","/1 шт\./","/2 шт\./","/3 шт\./","/4 шт\./","/5 шт\./","/6 шт\./","/7 шт\./","/8 шт\./","/9 шт\./","/0 шт\./","/1 л\./","/2 л\./","/3 л\./","/4 л\./","/5 л\./","/6 л\./","/7 л\./","/8 л\./","/9 л\./","/0 л\./","/1 гр\./","/2 гр\./","/3 гр\./","/4 гр\./","/5 гр\./","/6 гр\./","/7 гр\./","/8 гр\./","/9 гр\./","/0 гр\./","/;#/","/; #/","/Ингредиенты:/","/Приготовление\W?/","/<br\/>#/","/<br\/><br\/>/","/#/","/<p><h4>/","/<\/h4><\/p>/","/<p><\/p>/");
        $replacement = array("#","#","<h4>$1</h4><p>"," кг.;#"," шт.;#"," миллилитров;#"," гр.;#"," л.;#"," ","1 столовая ложка","2 столовых ложки","3 столовых ложки","4 столовых ложки","5 столовых ложек","6 столовых ложек","7 столовых ложек","8 столовых ложек","9 столовых ложек","0 столовых ложек","1 чайная ложка","2 чайных ложки","3 чайных ложки","4 чайных ложки","5 чайных ложек","6 чайных ложек","7 чайных ложек","8 чайных ложек","9 чайных ложек","0 чайных ложек","1 штука","2 штуки","3 штуки","4 штуки","5 штук","6 штук","7 штук","8 штук","9 штук","0 штук","1 литр","2 литра","3 литра","4 литра","5 литров","6 литров","7 литров","8 литров","9 литров","0 литров","1 грамм","2 грамма","3 грамма","4 грамма","5 грамм","6 грамм","7 грамм","8 грамм","9 грамм","0 грамм",";<br/>",";<br/>","<em>Ингредиенты:</em>","<em>Приготовление:</em> ","<br/>","<br/>#","</p><p>","<h4>","</h4>","");

        //делается только через цикл, ибо последовательность операций важна!
        for ($i = 0; $i < count($pattern); $i++) {
            $text = preg_replace($pattern[$i], $replacement[$i], $text);
        }

        $formatted_text = trim("<p>".$text."</p>");
        $formatted_text = preg_replace("/<p><\/p>\s{0,}?/", "", $formatted_text);
        
        return $formatted_text;
    }

/*######################################################################################################################
                                             get_article_symbols_amount($article_content)
######################################################################################################################*/
    private function get_article_symbols_amount($article_content) {
        //очищаем от тэгов
        $_text = strip_tags($article_content);
        //убираем пробелы
        $_text = preg_replace("/ /","",$_text);
        //считаем количество символов без пробелов
        $_length = mb_strlen($_text,'UTF-8');
        //возвращаем значение длины
        return $_length;
    }

/*######################################################################################################################
                                             get_article_price($article_symbols, $article_author_id)
######################################################################################################################*/
    private function get_article_price($article_symbols, $article_author_id) {
        //получаем данные автора
        $this->get_author_data($article_author_id);
        //высчитываем цену
        $_price = floatval($article_symbols) * floatval($this->author_price) * 0.001;
        //возвращаем значение
        return floatval($_price);
    }

/*######################################################################################################################
                                             current_payment()
######################################################################################################################*/
    private function current_payment() {
        $_sql = "select sum(`article_price`) as `total` from `" . $this->db_name . "`.`" . $this->db_article . "` ";
        $_sql.= "where `article_author_id` = " . $_GET['user_editor'];

        $mysql  = new mysql\Mysql;
        $mysql->connect();
        $mysql->sql($_sql);
        $values = $mysql->getResult();
        $mysql->disconnect();

        return floatval($values[0]['total']);
    }

/*######################################################################################################################
                                             check_for_admin()
######################################################################################################################*/
    public function check_for_admin() {
        if (isset($_GET['user_editor'])) {
            $_payment = $this->current_payment();
            $this->right_side_column = "<div class=\"gradient_line\"></div>";
            $this->right_side_column.= "<div id=\"to_pay\">К выплате: " . $_payment . " рублей</div>";
            $this->right_side_column.= "<div class=\"gradient_line\"></div>";
            $this->right_side_column.= "<div id=\"add_article\" class=\"category_0\" onclick=\"add_article();\">Добавить запись</div>";
            $this->right_side_column.= "<div class=\"gradient_line\"></div>";
            
            $this->get_author_data($_GET['user_editor']);

            $this->admin = true;
            return true;
        }
        $this->admin = false;
        return false;
    }
    
/*######################################################################################################################
                                          get_chosen_article_code()
######################################################################################################################*/
    public function get_chosen_article_code() {
        if (!isset($_GET['article']) || empty($_GET['article'])) {
            $_article = null;
            return $_article;
        } elseif (strpos($_GET['article'] , "/")) {
            $_article = null;
            return $_article;
        }
        $_article = $_GET['article'];
        
        $this->sql_string = "select article_id, article_keywords, article_name, article_picture, article_content from `" . $this->db_name . "`.`" . $this->db_article;
        $this->sql_string.= "` where `article_link` = '" . $_article . "';";
        
        $mysql  = new mysql\Mysql;
        $mysql->connect();
        $mysql->sql($this->sql_string);
        $values = $mysql->getResult();
        $mysql->disconnect();

        $_full_article      = $values[0][article_content];
        $findme             = '</p>';
        $pos                = strpos($_full_article, $findme);
        $_short_description = substr($_full_article, 3, ($pos - 3));

        $values[0][article_short_descr] = $_short_description;

        return $values[0];
    }

/*######################################################################################################################
                                         get_chosen_category_code()
######################################################################################################################*/
    public function get_chosen_category_code() {
        
        if (!isset($_GET['category'])) {
            $_category = null;
            return $_category;
        } 
        if (strpos($_GET['category'] , "age-")) {
            $_GET['page'] = substr($_GET['category'], 5);
            $_GET['category'] = 'vse_diety';
        }

        isset($_GET['page']) ? $this->current_page = $_GET['page'] : $this->current_page = 1;

        $_category = $_GET['category'];
        
        $this->sql_string = "select category_id, category_name, category_link, category_keywords from `" . $this->db_name . "`.`" . $this->db_category;
        $this->sql_string.= "` where `category_link` = '" . $_category . "';";
        
        $mysql = new mysql\Mysql;
        $mysql->connect();
        $mysql->sql($this->sql_string);
        $values = $mysql->getResult();
        $mysql->disconnect();
        
        return $values[0];
    }
   
/*######################################################################################################################
                                            add_article($array)
######################################################################################################################*/
    public function add_article($array) {
        extract($_POST);
         
        //присваиваем значения переменным
        $article_author_id = $author;
        $article_name = $name;
        $article_picture = $picture . ".jpg";
        $article_content = $this->format_text($content);
        $article_category_0 = $category_0;
        $article_category_1 = $category_1;
        $article_category_2 = $category_2;
        $article_link = $link;
        $article_date = time();
        $article_reads = 0;
        $article_comments = 0;
        $article_rating = 0;
        $article_keywords = $this->get_keywords($article_content);
        $article_symbols = $this->get_article_symbols_amount($article_content);
        $article_price = floatval($this->get_article_price($article_symbols, $article_author_id));

        // проверяем наличие дубликатов имен и исправляем их в случае необходимости
        $this->sql_string = "select article_id from `" . $this->db_name . "`.`" . $this->db_article . "` ";
        $this->sql_string.= "where `article_link`='" . $link . "' ";
        for ($i = 1; $i < 19; $i++) {
            $this->sql_string.= "or `article_link`='" . $link . '-' . $i . "' ";
        }
        $this->sql_string.= "or `article_link`='" . $link . '-' . "20';";
        
        /* mysql request */
        $mysql = new mysql\Mysql;
        $mysql->connect();
        $mysql->sql($this->sql_string);
        $val = $mysql->getResult();
        $mysql->disconnect();
        
        // Есть дубликаты? Тогда добавляем одну букву
        if (count($val) > 0) $article_link .= '-' . count($val);
        
        //формируем MySQL строку и добавляем в базу данных
        $this->sql_string = "insert into";
        $this->sql_string.= " `" . $this->db_name . "`.`" . $this->db_article . "` ";
        $this->sql_string.= "(article_author_id, article_name, article_picture, article_content, ";
        $this->sql_string.= "article_category_0, article_category_1, article_category_2, ";
        $this->sql_string.= "article_link, article_date, article_reads, article_comments, article_rating, article_keywords, ";
        $this->sql_string.= "article_symbols, article_price) values (";
        $this->sql_string.= $article_author_id . ", '" . $article_name . "', '" . $article_picture . "', '";
        $this->sql_string.= $article_content . "', '" . $article_category_0 . "', '" . $article_category_1. "', '" . $article_category_2 . "', '";
        $this->sql_string.= $article_link . "', " . $article_date . ", ";
        $this->sql_string.= $article_reads . ", " . $article_comments . ", " . $article_rating . ", '" . $article_keywords . "',";
        $this->sql_string.= $article_symbols . ", " . $article_price . ");";

        echo $this->sql_string;
        
        /* mysql request */
        $mysql = new mysql\Mysql;
        $mysql->connect();
        $mysql->sql($this->sql_string);
        $val = $mysql->getResult();
        $mysql->disconnect();
    }
    
/*######################################################################################################################
                                          load_article($array)
######################################################################################################################*/
    public function load_article($array) {
        extract($_POST);
        
        $this->sql_string = "select * from `" . $this->db_name . "`.`" . $this->db_article . "` ";
        $this->sql_string.= "where `article_id`=" . $id;
        
        /* mysql request */
        $mysql = new mysql\Mysql;
        $mysql->connect();
        $mysql->sql($this->sql_string);
        $val = $mysql->getResult();
        $mysql->disconnect();
        
        echo json_encode($val);
    }
    
/*######################################################################################################################
                                            edit_article($array)
######################################################################################################################*/
    public function edit_article($array) {
        extract($_POST);
        
        $this->sql_string = "update `" . $this->db_name . "`.`" . $this->db_article . "` set ";
        $this->sql_string.= "`article_category_0`='" . $category_0 . "', ";
        $this->sql_string.= "`article_category_1`='" . $category_1 . "', ";
        $this->sql_string.= "`article_category_2`='" . $category_2 . "', ";
        $this->sql_string.= "`article_content`='" . $content . "', ";
        if (isset($picture)) $this->sql_string.= "`article_picture`='" . $picture . ".jpg', ";
        $this->sql_string.= "`article_name`='" . $name . "', ";
        $this->sql_string.= "`article_keywords`='" . $keywords . "' ";
        $this->sql_string.= "where `article_id`=" . $id . ";";
        
        /* mysql request */
        $mysql = new mysql\Mysql;
        $mysql->connect();
        $mysql->sql($this->sql_string);
        $val = $mysql->getResult();
        $mysql->disconnect();
    }
    
/*######################################################################################################################
                                        show_page($_category, $_article)
######################################################################################################################*/
    public function show_page($_category, $_article) {
        $this->current_category_code = $_category;
        if (!isset($_article) || empty($_article)) {
            $this->get_category_data($_category);
            $this->show_category();
            return $this->output_page;
        } elseif (isset($_article) && !empty($_article)) {
            $this->get_article_data($_article);
            $this->show_article();
            return $this->output_page;
        }
    }
/*######################################################################################################################
                               построение списка для выдачи категорий в правую колонку
######################################################################################################################*/
    public function get_category() {
        global $server, $main_page;
        $this->sql_string = "select * from `" . $this->db_name . "`.`" . $this->db_category;
        $this->sql_string.= "` where category_show=true order by category_id asc";
        
        $mysql = new mysql\Mysql;
        $mysql->connect();
        $mysql->sql($this->sql_string);
        $values = $mysql->getResult();
        $mysql->disconnect();
        
        $categories_amount = count($values);
        
        for ($i = 0; $i < $categories_amount; $i++) {
            if ($values[$i]["category_id"] != "00" & $values[$i]["category_id"] != "01") {
                //выбор подзаголовка
                if (substr($values[$i]["category_id"], 1, 1) == "0") {
                    if ($i > 3) {
                        $this->right_side_column.= "<div class=\"gradient_line\"></div>";
                    }
                    $this->right_side_column.= "<a href=\"\">";
                    $this->right_side_column.= "<div class=\"category_0\">";
                    $this->right_side_column.= $values[$i]["category_name"] . "</div></a>";
                } else {
                    //выделение текущей категории
                    if ($_GET['category'] == $values[$i]["category_link"]) {
                        $this->right_side_column.= "<a href=\"" . $server . $main_page . $values[$i]["category_link"] . "/page-1/\">";
                        $this->right_side_column.= "<div class=\"category_selected\">";
                        $this->right_side_column.= $values[$i]["category_name"] . "</div></a>";
                    //все остальные (невыбранные)
                    } else {
                        $this->right_side_column.= "<a href=\"" . $server . $main_page . $values[$i]["category_link"] . "/page-1/\">";
                        $this->right_side_column.= "<div class=\"category_2\">";
                        $this->right_side_column.= $values[$i]["category_name"] . "</div></a>";
                    }
                }
             }
        }
        return $this->right_side_column;
    }
 /*######################################################################################################################
                          построение списка SELECT для окна редактирования статей
######################################################################################################################*/
    public function get_selected_categories() {
        $select_category = "select * from `" . $this->db_name . "`.`" . $this->db_category;
        $select_category.= "` where category_show = true order by category_id asc";
        
        $mysql = new mysql\Mysql;
        $mysql->connect();
        $mysql->sql($select_category);
        $values = $mysql->getResult();
        $mysql->disconnect();
        
        $array_lenght = count($values);
        $array_select = array();
        
        for($a = 0; $a < 3; $a++){
            $select_category = "<select id=\"article_category_".$a."\" name=\"article_category\">";
            for ($i = 1; $i < $array_lenght; $i++) {
                if (substr($values[$i]["category_id"], 1, 1) == "0") {
                    $select_category.= "<optgroup label=\"" . $values[$i]["category_name"] . "\">";
                    $counter = 0;
                    $counter++;
                    if ($counter > 3) {
                        $select_category.= "</optgroup>";
                    }
                } else {
                    $select_category.= "<option class=\"option\" value=\"";
                    $select_category.= $values[$i]["category_id"];
                    $select_category.= "\">";
                    $select_category.= $values[$i]["category_name"];
                    $select_category.= "</option>";
                }
            }
            $select_category.= "</select>";
            $array_select[$a] = $select_category;
        }
        return $array_select[0].$array_select[1].$array_select[2];
    }
}
?>