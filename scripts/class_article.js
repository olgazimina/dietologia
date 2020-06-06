$id = null;
/*######################################################################################################################
                                              add_article()
######################################################################################################################*/
function add_article() {
    if (document.querySelector("#shadow")) return;

    Article = Create_Article("add");
    Article.toggle_fields();
    Article.open_window();
    Article.enable_buttonset();
}

/*######################################################################################################################
                                              edit_article(_id)
######################################################################################################################*/
function edit_article(_id) {
    $id = _id;
    if (document.querySelector("#shadow")) return;

    Article = Create_Article("load");
    Article.toggle_fields();
    Article.open_window();
    Article.enable_buttonset();
    Article.load_article(_id);
}

/*######################################################################################################################
                                              Create_Article(flag)
######################################################################################################################*/
function Create_Article(flag) {
    var obj = new Object();

    obj.flag = flag;

    obj.article_id = $id;

    obj.toggle_fields = function() {
        var inputs = document.getElementsByTagName('input');
        for (var i = inputs.length, n = 0; n < i; n++) {
            inputs[n].disabled = !inputs[n].disabled;
        }
    }

    obj.open_window = function() {

        var Window = new Object();
        Window.shadow = document.createElement("div");
        Window.shadow.id = "shadow";
        Window.main = document.createElement("div");
        Window.main.id = "main_window";
        Window.main.innerHTML = main_window_code;

        /**
         * enabling fading effect which doesn't work with
         * standart transition effect in css. Need setTimeout
         */
        document.body.appendChild(Window.shadow);
        document.body.appendChild(Window.main);
        setTimeout(function() {
            Window.shadow.className = "enabled_shadow";
        }, 1);
    }

    obj.load_article = function(_id) {
        var server_script = "scripts_php/ajax_add_article.php";
        var request = "id=" + obj.article_id;
        request += "&flag=" + obj.flag;

        var ajax = new Ajax(server_script, request);
        ajax.send_request(function($response) {
            $data = JSON.parse($response);
            document.querySelector("#article_name").value = $data[0].article_name;
            document.querySelector("#article_content").value = $data[0].article_content;
            document.querySelector("#article_keywords").value = $data[0].article_keywords;
            document.querySelector("#article_category_0").value = $data[0].article_category_0;
            document.querySelector("#article_category_1").value = $data[0].article_category_1;
            document.querySelector("#article_category_2").value = $data[0].article_category_2;
            document.querySelector("#author_id").value = $data[0].article_author_id;
        });

        /*
         * флаг необходимо поменять на запись 
         */
        obj.flag = "edit";
    }

    obj.enable_buttonset = function() {
        upload_button = document.querySelector("#my_send");
        cancel_button = document.querySelector("#cancel");
        /**
         * enable mouse click
         */
        upload_button.addEventListener("click", function() {
            obj.button_ok();
        });
        cancel_button.addEventListener("click", function() {
            obj.button_cancel();
        });
    }

    obj.button_ok = function() {
        var Window = obj.grab_data(); if (Window == false) return;
        var Picture = Window.art_link + "-" + Window.timestamp;
        if (typeof Window.apicture == 'undefined' || Window.apicture == '' && obj.flag == "edit") {
            obj.mysql_request(Window);
        } else if (typeof Window.apicture != 'undefined' && obj.flag == "edit") {
            obj.mysql_request(Window);
            obj.upload_picture(Picture);
        } else if (obj.flag == "add") {
            obj.mysql_request(Window);
            obj.upload_picture(Picture);
        }
        Window.w_status = obj.close_window();
    }

    obj.button_cancel = function() {
        obj.close_window();
    }

    obj.translit = function(ru_string) {
        //Если с английского на русский, то передаём вторым параметром true.
        var
            rus = "\"#!#'#;#:#?#.#,#-#щ####ш##ч##ц##ю##я##ё##ж##ъ##ы##э##а#б#в#г#д#е#з#и#й#к#л#м#н#о#п#р#с#т#у#ф#х##ь# ".split(/#+/g),
            eng = "`##`#`#`#`#`#`#`#-#shсh#sh#ch#ts#yu#ya#yo#zh#`##y##e##a#b#v#g#d#e#z#i#y#k#l#m#n#o#p#r#s#t#u#f#kh#`#-".split(/#+/g);
        return function(text, engToRus) {
            var x;
            engToRus = false;
            for (x = 0; x < rus.length; x++) {
                if (eng[x] == "`") eng[x] = "";
                text = text.split(engToRus ? eng[x] : rus[x]).join(engToRus ? rus[x] : eng[x]);
                text = text.split(engToRus ? eng[x].toUpperCase() : rus[x].toUpperCase()).join(engToRus ? rus[x] : eng[x]);
            }
            return text;
        }
    }();

    obj.grab_data = function() {
        var _data = new Object();
        var reset_flag = false;

        _data.category_0 = function() {
            var element_0 = document.querySelector("#article_category_0");
            var category_0 = element_0.options[element_0.selectedIndex].value;
            if (category_0 == "01" || category_0.substr(0, 1) == 0) {
                element_0.style.borderColor = "#d44";
                reset_flag = true;
            }
            return category_0;
        }();

        _data.category_1 = function() {
            var element_1 = document.querySelector("#article_category_1");
            var category_1 = element_1.options[element_1.selectedIndex].value;
            element_1.style.borderColor = "#d44";
            return category_1;
        }();

        _data.category_2 = function() {
            var element_2 = document.querySelector("#article_category_2");
            var category_2 = element_2.options[element_2.selectedIndex].value;
            element_2.style.borderColor = "#d44";
            return category_2;
        }();

        _data.art_name = function() {
            var name = document.querySelector("#article_name");
            if (name.value == "") {
                name.style.borderColor = "#d44";
                reset_flag = true;
            }
            return name.value;
        }();

        _data.timestamp = function() {
            _timestamp = Date.now() / 1000 | 0;
            return _timestamp;
        }();

        _data.art_link = function() {
            var _link = obj.translit(_data.art_name);
            return _link;
        }();

        _data.a_author = document.querySelector("#author_id").value;

        _data.apicture = function(){
            var picture = document.querySelector("#my_id_file").value;
            if (obj.flag == "add") {
                if (picture == '' || typeof picture == 'undefined') {
                    reset_flag = true;
                    alert("Не выбрана картинка");
                }
            }
            return picture;
        }();

        _data.acontent = function() {
            var content = document.querySelector("#article_content");
            if (content.value == "") {
                content.style.borderColor = "#d44";
                reset_flag = true;
            }
            return content.value;
        }();

        _data.keywords = function() {
            var keywords = document.querySelector("#article_keywords");
            if (keywords.value == "") {
                keywords.style.borderColor = "#d44";
                reset_flag = true;
            }
            return keywords.value;
        }();        

        _data.w_status = true;

        _data.callback = false;

        if (reset_flag) return false;
        if (!reset_flag) return _data;
    }

    obj.mysql_request = function(_data) {
        var server_script = "scripts_php/ajax_add_article.php";
        var request = "id=" + obj.article_id;
        request += "&category_0=" + _data.category_0;
        request += "&category_1=" + _data.category_1;
        request += "&category_2=" + _data.category_2;
        request += "&content=" + _data.acontent;
        request += "&name=" + _data.art_name;
        request += "&author=" + _data.a_author;
        request += "&link=" + obj.translit(_data.art_name);
        request += "&flag=" + obj.flag;
        request += "&keywords=" + _data.keywords;
        if (_data.apicture != '') request += "&picture=" + obj.translit(_data.art_name) + "-" + _data.timestamp;

        var ajax = new Ajax(server_script, request);
        ajax.send_request("");
    }


    obj.upload_picture = function(file_name) {
        var my_reader = null,
            my_interval = null,
            my_queryHttp = 0,
            my_HTTP = f_createXmlHttp(),
            my_file,
            min = 1000,
            max = 2000000;

        my_file = document.getElementById("my_id_file").files;
        if (!my_file) {
            alert('Смените браузер на более новую версию, или на современный.');
            return false;
        }
        my_file = my_file[0];
        if (!my_file || !my_file.name) {
            alert('Укажите (выберите) файл');
            return false;
        }
        if (my_file.size < min || my_file.size > max) {
            alert('размер файла вне допустимых размеров');
            return false;
        }
        my_reader = new FileReader();
        my_reader.readAsDataURL(my_file); // содержимое любого файла в бинарном виде
        my_reader.onloadend = function() {
            var b = (Math.round(Math.random() * 90000000)).toString(),
                r = '--' + b + '\r\n',
                t;
            if (!my_HTTP || my_queryHttp != 0) return;
            try {
                my_queryHttp = 1;
                my_HTTP.open('POST', 'scripts_php/ajax_upload_images.php', true);
                my_HTTP.onreadystatechange = null;
                my_HTTP.setRequestHeader('Content-Type', 'multipart/form-data; boundary=' + b);
                t = r + 'Content-Disposition: form-data; name="uplfile"; filename="' + file_name + '";\r\nContent-Type: ' + my_file.type + '\r\n\r\n';
                t += my_reader.result + '\r\n' + '--' + b + '--\r\n';
                t = 'Content-Length: ' + t.length + '\r\n\r\n' + t;
                my_HTTP.send(t);
            } catch (e) {
                my_queryHttp = 0;
                alert('Ошибка. Не удалось отправить POST-запрос.');
            }
        }
        my_interval = setInterval(function() {
            f_my_RequestStateChange();
        }, 100);


        function f_my_RequestStateChange() { // здесь все небходимое для принятия ответа сервера
            var ret, st;
            try {
                if (my_HTTP.readyState == 4) {
                    st = my_HTTP.status;
                    if (st == 200) {
                        ret = my_HTTP.responseText;
                    } else alert('network error. status:' + st.toString());
                    my_queryHttp = 0;
                    my_reader = null;
                    if (my_interval) clearInterval(my_interval);
                    my_interval = null;
                }
            } catch (e) {
                my_reader = null;
                if (my_interval) clearInterval(my_interval);
                my_interval = null;
                alert('Ошибка приема ответа');
            }
        }

        function f_createXmlHttp() {
            var i, v, x, a = 'MSXML2.',
                b = 'XMLHTTP';
            try {
                x = new XMLHttpRequest();
            } catch (e) {
                v = new Array(a + b + '.6.0', a + b + '.5.0', a + b + '.4.0', a + b + '.3.0', a + b, 'Microsoft.' + b);
                a = v.length;
                for (i = 0; i < a && !x; i++) {
                    try {
                        x = new ActiveXObject(v[i]);
                    } catch (e) {}
                }
            }
            return (!x) ? false : x;
        }
    }

    obj.close_window = function() {
        var Window = new Object();
        Window.shadow = document.querySelector("#shadow");
        Window.main = document.querySelector("#main_window");
        document.querySelector("#my_send").removeEventListener("click", function() {
            obj.button_ok();
        });
        document.querySelector("#cancel").removeEventListener("click", function() {
            obj.button_cancel();
        });
        document.body.removeChild(Window.main);
        document.body.removeChild(Window.shadow);
        obj.toggle_fields();

        // возвращает статус окна - закрыто или открыто
        return false;
    }
    return obj;
}
