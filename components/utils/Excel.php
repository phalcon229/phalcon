<?php
namespace Components\Utils;
class Excel
{
    /**
     * 生成excel表格
     * @param array $title 表格的 th
     * @param array $data 表格数据 td
     */
    public static function getExcel($title = array(), $data = array())
    {
        header("Content-type:application/vnd.ms-excel");
        header("Content-Disposition:attachment;filename=" . date('Y年m月d日') . ".xls");
        header('Pragma: no-cache');
        header('Expires: 0');

         // echo iconv('utf-8', 'gbk', implode("\t", $title)), "\n"; //转码需要权限 chmod -R 777
        // echo implode("\t", $title), "\n";
        echo mb_convert_encoding(implode("\t", $title), 'gbk') . "\n";
        if(!empty($data))
        {
            foreach ($data as $value) {
                // echo iconv('utf-8', 'gbk', implode("\t", $value)), "\n";
                echo mb_convert_encoding(implode("\t", $value), 'gbk') . "\n";
                // echo implode(",", $value), "\n";
            }
        }else{
            echo mb_convert_encoding('抱歉，没有找到相应结果', 'gbk');
        }
    }
}