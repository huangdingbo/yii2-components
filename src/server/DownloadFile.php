<?php


namespace dsj\components\server;


use yii\web\ForbiddenHttpException;

class DownloadFile
{
    private $filePath = null;

    public function setFilePath($filePath){
        $this->filePath = $filePath;

        return $this;
    }

    private function check(){
        if (!$this->filePath){
            throw new ForbiddenHttpException('请先设置要下载的文件');
        }

        if (!file_exists($this->filePath)){
            throw new ForbiddenHttpException("文件{$this->filePath}不存在");
        }
    }

    public function run(){
        //检查
        $this->check();

        //设置脚本的最大执行时间，设置为0则无时间限制
        set_time_limit(0);
        ini_set('max_execution_time', '0');

        //通过header()发送头信息
        //因为不知道文件是什么类型的，告诉浏览器输出的是字节流
        header('content-type:application/octet-stream');

        //告诉浏览器返回的文件大小类型是字节
        header('Accept-Ranges:bytes');

        //获得文件大小
        //$filesize = filesize($filename);//(此方法无法获取到远程文件大小)
//        $header_array = get_headers($this->fileName, true);
//        $filesize = $header_array['Content-Length'];
        $filesize = filesize($this->filePath);

        //告诉浏览器返回的文件大小
        header('Accept-Length:'.$filesize);
        //告诉浏览器文件作为附件处理并且设定最终下载完成的文件名称
        header('content-disposition:attachment;filename='.basename($this->filePath));

        //针对大文件，规定每次读取文件的字节数为4096字节，直接输出数据
        $read_buffer = 1024;
        $handle = fopen($this->filePath, 'rb');
        //总的缓冲的字节数
        $sum_buffer = 0;
        //只要没到文件尾，就一直读取
        while(!feof($handle) && $sum_buffer<$filesize) {
            echo fread($handle,$read_buffer);
            $sum_buffer += $read_buffer;
        }

        //关闭句柄
        fclose($handle);
        exit;
    }
}