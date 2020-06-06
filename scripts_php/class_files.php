<?php
/**
 * примеры:
 ************************************************************
 * сохранение любого файла
 ************************************************************
 * $new_file = new Upload_Any_File();
 * $new_file->get_file();
 * $new_file->set_path("../pictures/");
 * $new_file->save_file();
 * unset($new_file);
 * 
 ************************************************************
 * сохранение картинки форматированной
 ************************************************************
 * $new_image = new Upload_Image();
 * $new_image->get_file();
 * $new_image->set_dimensions(200,200,"../pictures/200/");
 * $new_image->save_image();
 * $new_image->set_dimensions(100,100,"../pictures/100/");
 * $new_image->save_image();
 * unset($new_image);
 */

class Upload_File
{
    public $path = null;
    public $name = null;
    public $file = null;
    public $image = null;
    public $img_w = null;
    public $img_h = null;
    public $source = null;
    public $target = null;

    private $temp = "../files/";
    
    public function get_file() {
        if (isset($_FILES['uplfile'])) {
            
            /**
             * перемещение оригинального файла в нужную директорию
             */
            $this->name = basename($_FILES['uplfile']['name']);
            $this->source = $_FILES['uplfile']['tmp_name'];
            $this->target = $this->temp . $this->name;
            $moved_ok = move_uploaded_file($this->source, $this->target);
        }
        
        /**
         * если перемещение файла произошло успешно, тогда обрезание
         * Вырезка служебных символов "data: ... ;base64,"
         * переменная this->file содержит сам файл (происходит очистка)
         */
        if ($moved_ok) {
            $this->file = @file_get_contents($this->target);
            $position = strpos($this->file, ';base64,');
            if (!$position) {
                print 'error (pos)';
                exit;
            }
        }
        
        /**
         * декодирование
         */
        $this->file = base64_decode(substr($this->file, $position + 8));
    }
    
    public function save_image() {
        /**
         * переопределение переменной this->target
         */
        $this->target = $this->path.$this->name;
        
        /**
         * теперь производится форматирование и запись файла
         * в зависимости от формата оригинала создаем изображение в этом же формате.
         * Необходимо для последующего сжатия
         */
        $this->image = imagecreatefromstring($this->file);
        
        /**
         * СОЗДАНИЕ КВАДРАТНОГО ИЗОБРАЖЕНИЯ И ЕГО ПОСЛЕДУЮЩЕЕ СЖАТИЕ
         * Создание квадрата 100x100
         * destination_image - результирующее изображение
         * this->img_w - ширина изображения
         * ratio - коэффициент пропорциональности
         */
        
        // создаём исходное изображение на основе
        // исходного файла и определяем его размеры
        $w_src = imagesx($this->image);

        //вычисляем ширину
        $h_src = imagesy($this->image);
        
        //вычисляем высоту изображения
        
        // создаём пустую квадратную картинку
        // важно именно truecolor!, иначе будем иметь 8-битный результат
        $destination_image = imagecreatetruecolor($this->img_w, $this->img_h);
        
        // вырезаем квадратную серединку по x, если фото горизонтальное
        if ($w_src > $h_src) $resampled = imagecopyresampled($destination_image, $this->image, 
            0, 0, round((max($w_src, $h_src) - min($w_src, $h_src)) / 2), 0, 
            $this->img_w, $this->img_h, min($w_src, $h_src), min($w_src, $h_src));
        
        // вырезаем квадратную серединку по y,
        // если фото вертикальное (хотя можно тоже серединку)
        if ($w_src < $h_src) $resampled = imagecopyresampled($destination_image, $this->image, 
            0, 0, 0, round((max($w_src, $h_src) - min($w_src, $h_src)) / 2), 
            $this->img_w, $this->img_h, min($w_src, $h_src), min($w_src, $h_src));
        
        // квадратная картинка масштабируется без вырезок
        if ($w_src == $h_src) $resampled = imagecopyresampled($destination_image, $this->image, 
            0, 0, 0, 0, 
            $this->img_w, $this->img_h, $w_src, $h_src);
        
        //вычисляем время в настоящий момент.
        $date = time();
        
        //сохраняем изображение формата jpg в нужную папку, именем будет текущее время.
        //почему именно jpg? Он занимает очень мало места + уничтожается анимирование gif изображения. 
        imagejpeg($destination_image, $this->path . $this->name . ".jpg");
        
        //заносим в переменную путь до аватара.
        $delfull = $this->temp . $this->name;

        //удаляем оригинал загруженного изображения, он нам больше не нужен. Задачей было - получить миниатюру.
        unlink($delfull);
    }
    
    public function save_file() {
        /**
         * создание пути и имени для сохранения
         */
        $this->target = $this->path.$this->name;
        /**
         * сохранение файла
         */
        $binary_file = fopen($this->target, 'w');
        if ($binary_file == False) {
            print 'error (binary_file)';
            exit;
        }
        rewind($binary_file);
        if (-1 == fwrite($binary_file, $this->file)) {
            print 'error (this->file)';
            fclose($binary_file);
            exit;
        }
        ftruncate($binary_file, ftell($binary_file));
        fflush($binary_file);
        fclose($binary_file);
        print 'image uploaded ok';
    }
}

class Upload_Image extends Upload_File
{
    
    function set_dimensions($width,$height,$path)
    {
        $this->img_w = $width;
        $this->img_h = $height;
        $this->path  = $path;
    }
}
class Upload_Any_File extends Upload_File
{
    
    function set_path($path)
    {
        $this->path  = $path;
    }
}
?>