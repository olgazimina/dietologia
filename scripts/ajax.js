function Ajax(serv_script, sndng_data) {
    var o_ajax = new Object();
    this.server_script = serv_script;
    this.sending_data = sndng_data;
    this.sync_type = true;
    this.xhr_data = {
        "post_data": "",
        "response": null
    };
    xhr = null;

    this.send_request = function(_callback) {
        xhr = function f_createXmlHttp() {
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
        }();

        var tmp = this.sending_data.split("&");
        for (var i = 0; i < tmp.length; i++) {
            var pair = tmp[i].split("=");
            this.xhr_data.post_data += encodeURIComponent(pair[0]) + "=" + encodeURIComponent(pair[1]);
            this.xhr_data.post_data += "&";
        };

        xhr.open("POST", this.server_script, this.sync_type);
        xhr.setRequestHeader("Content-Type","application/x-www-form-urlencoded;charset=utf-8");
        xhr.send(this.xhr_data.post_data);

        xhr.onreadystatechange = f_RequestStateChange(_callback);

        my_interval = setInterval(function() {
            xhr.onreadystatechange = f_RequestStateChange(_callback);
        }, 100);
    }
    
    function f_RequestStateChange(__callback) {
        var ret, st;
        try {
            if (xhr.readyState == 4) {
                st = xhr.status;
                if (st == 200) {
                    ret = xhr.responseText;
                    if (!__callback) {
                        return;
                    } else {
                        __callback(ret);
                    }
                } else alert('network error. status:' + st.toString());
                my_queryHttp = 0;
                my_reader = null;
                //if (my_interval) clearTimeout(my_interval);
                if (my_interval) clearInterval(my_interval);
                my_interval = null;
            }
        } catch (e) {
            my_reader = null;
            //if (my_interval) clearTimeout(my_interval);
            //if (my_interval) clearInterval(my_interval);
            my_interval = null;
            alert('Ошибка приема ответа');
        }
    }
}
