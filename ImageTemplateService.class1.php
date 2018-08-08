<?php
/**
 * Created by PhpStorm.
 * User: 爱武
 * Date: 2017/8/6
 * Time: 下午 12:36
 */

namespace Asset\Lib;


require(SITE_PATH.'/simplewind/Core/Library/Vendor/barcodegen/class/BCGFontFile.php');
// require(SITE_PATH.'/simplewind/Core/Library/Vendor/barcodegen/class/BCGColor.php');
require(SITE_PATH.'/simplewind/Core/Library/Vendor/barcodegen/class/BCGDrawing.php');
require(SITE_PATH.'/simplewind/Core/Library/Vendor/barcodegen/class/BCGcode128.barcode.php');

class ImageTemplateService
{
    /*
    * 创建128条形码
    * @param: string: test 条形码数据,int: scale 条形码缩放比例
    * @return BCGDrawing对象 ：drawing
    * @font  显示不显示字符串
    */
    private static function creatBarcode128($text='',$scale=1,$thickness=35,$font=1) {
        //如果需要显示条形码的内容
        if($font)
            $font = new \BCGFontFile(SITE_PATH.'/simplewind/Core/Library/Vendor/barcodegen/font/Arial.ttf', $scale*7);
        $colorFront = new \BCGColor(0, 0, 0);
        $colorBack = new \BCGColor(255, 255, 255);
// Barcode Part
        $code = new \BCGcode128();
        $code->setScale($scale);
        $code->setThickness($thickness);
        $code->setForegroundColor($colorFront);
        $code->setBackgroundColor($colorBack);
        $code->setFont($font);
        $code->setStart(NULL);
        $code->setTilde(true);
        $code->parse($text);

// Drawing Part
        $drawing = new \BCGDrawing('', $colorBack);
        $drawing->setBarcode($code);
        $drawing->draw();

        return $drawing;
    }

    /**
     * 生成寄件助手小程序二维码图片
     * @param $qrcode_path
     * @return mixed
     */
    public static function printQrcode($uid,$qrcode_path,$site_name,$phone){
        $fontfile = SITE_PATH."/data/font/msyh.ttf";
        $save_image_dir = SITE_PATH.'/data/TempletImage/wxa/';
        $fileName = 'wx_qr_'.$uid.'.png';
        $fontSize = 32;

        //从文件中读取背景
        $img=imagecreatefromjpeg(SITE_PATH."data/ExpressImage/template/mina_qrcode.jpg");
        $blackColor = imagecolorallocate($img, 0, 0, 0);

//        //创建一个颜色
//        $whiteColor = imagecolorallocate($img, 255, 255, 255);
//        $blackColor = imagecolorallocate($img, 0, 0, 0);
//
        if (file_exists($save_image_dir.$fileName)) {
            return $save_image_dir.$fileName;
        }
        //将二维码写入模板
        $qr_img = imagecreatefrompng($qrcode_path);//返回图像标识符
        //创建一个新的图像源（目标图像）
        $new_width=567;
        $n_qr_img=imagecreatetruecolor($new_width,$new_width);
         //执行等比缩放
        imagecopyresampled($n_qr_img,$qr_img,0,0,0,0,$new_width,$new_width,imagesx($qr_img),imagesy($qr_img));

        imagecopymerge($img,$n_qr_img, 120,165,0,0,imagesx($n_qr_img),imagesy($n_qr_img),100);

        //写入网点名和联系电话
        imagettftext ($img , $fontSize , 0 , 314,1186 , $blackColor ,  $fontfile , $site_name);
        imagettftext ($img , $fontSize , 0 , 314,1272 , $blackColor ,  $fontfile , $phone);

        if(!file_exists($save_image_dir)) {
            //可创建多级目录
            mkdir($save_image_dir);
            chmod($save_image_dir,0777);
            // mkdir($save_image_dir,0777,true);
        }
        imagefilter($img, IMG_FILTER_COLORIZE, 25, 25, 25);
        imagecolorallocate($img,0,0,0);
        imagecolortransparent($img,imagecolorallocate($img,0,0,0));
        //ImageTemplateService::imageGreyscale($img,0);
        imagepng($img,$save_image_dir.$fileName,9);
        //        //销毁该图片(释放内存)
        imagedestroy($n_qr_img);
        imagedestroy($qr_img);
        imagedestroy($img);
        return $save_image_dir.$fileName;
    }

    /**
     * 打印测试面单
     * @return string
     */
    public static function printTest(){
        $back_file_path='g2.jpg';
        $template_data=[
            ['type' => 'TEXT','start_x'=>480,'start_y'=>80,'font_size'=>46,'row_height'=>0,'row_count'=>0,'text'=>'测试业务'], // 快递业务类型,$data['eps_exp_type_name']

            ['type' => 'BARCODE','start_x'=>160,'start_y'=>225,'width'=>480,'height'=>100,'data'=>'1234567890'], // 条形码,

            ['type' => 'TEXT','start_x'=>90,'start_y'=>362,'font_size'=>23,'row_height'=>0,'row_count'=>0,'text'=>'张三丰 13779955840 三丰科技有限公司'], // 收件人姓名
            ['type' => 'TEXT','start_x'=>90,'start_y'=>526,'font_size'=>23,'row_height'=>0,'row_count'=>0,'text'=>'李无忌 13445566770 明教集团'], // 寄件人姓名

            ['type' => 'TEXT','start_x'=>10,'start_y'=>691,'font_size'=>23,'row_height'=>0,'row_count'=>0,'text'=>'付款方式:'.'现付'], // 付款方式
            ['type' => 'TEXT','start_x'=>10,'start_y'=>727,'font_size'=>23,'row_height'=>0,'row_count'=>0,'text'=>'计算重量(kg):'.'1kg'], // 计算重量
            ['type' => 'TEXT','start_x'=>10,'start_y'=>763,'font_size'=>23,'row_height'=>0,'row_count'=>0,'text'=>'保价金额(元):'], // 保价金额
            ['type' => 'TEXT','start_x'=>10,'start_y'=>799,'font_size'=>23,'row_height'=>0,'row_count'=>0,'text'=>'数量:'.'1'], // 数量
            ['type' => 'TEXT','start_x'=>10,'start_y'=>835,'font_size'=>23,'row_height'=>0,'row_count'=>0,'text'=>'签回单:'.'否'], // 签回单
            ['type' => 'TEXT','start_x'=>10,'start_y'=>862,'font_size'=>14,'row_height'=>0,'row_count'=>0,'text'=>'打印时间  ' . date('Y-m-d H:i:s', time())], // 打印时间

            ['type' => 'TEXT','start_x'=>450,'start_y'=>691,'font_size'=>23,'row_height'=>0,'row_count'=>0,'text'=>'签收人:'], // 签收人
            ['type' => 'TEXT','start_x'=>450,'start_y'=>737,'font_size'=>23,'row_height'=>0,'row_count'=>0,'text'=>'签收时间:'], // 签收时间

            ['type' => 'BARCODE','start_x'=>320,'start_y'=>887,'width'=>440,'height'=>120,'data'=>'1234567890'], // 条形码,

            ['type' => 'TEXT','start_x'=>90,'start_y'=>1051,'font_size'=>23,'row_height'=>0,'row_count'=>0,'text'=>'张三丰 13779955840 三丰科技有限公司'], // 收件人姓名
            ['type' => 'TEXT','start_x'=>90,'start_y'=>1089,'font_size'=>23,'row_height'=>33,'row_count'=>22,'text'=>'福建省厦门市湖里区湖里大道星星花园107号1204室'], // 收件人地址
            ['type' => 'TEXT','start_x'=>90,'start_y'=>1191,'font_size'=>23,'row_height'=>0,'row_count'=>0,'text'=>'李无忌 13445566770 明教集团'], // 寄件人姓名
            ['type' => 'TEXT','start_x'=>90,'start_y'=>1229,'font_size'=>23,'row_height'=>33,'row_count'=>22,'text'=>'湖北省武汉市青山区明教大楼25层403室企业战略策划部'], // 寄件人地址

            ['type' => 'TEXT','start_x'=>10,'start_y'=>1332,'font_size'=>23,'row_height'=>0,'row_count'=>0,'text'=>'物品:'.'文件'], //物品
            ['type' => 'TEXT','start_x'=>10,'start_y'=>1378,'font_size'=>23,'row_height'=>33,'row_count'=>25,'text'=>'过往一片烟尘起，不及如今一封情！'], // 留言
        ];
        $template_data[] =['type' => 'TEXT','start_x'=>100,'start_y'=>187,'font_size'=>58,'row_height'=>0,'row_count'=>0,'text'=>'闽-厦-美']; // 大头笔
        //公司名
        $template_data[] =['type' => 'TEXT','start_x'=>90,'start_y'=>400,'font_size'=>23,'row_height'=>33,'row_count'=>22,'text'=>'福建省厦门市湖里区湖里大道星星花园107号1204室']; // 收件人地址
        $template_data[] =['type' => 'TEXT','start_x'=>90,'start_y'=>564,'font_size'=>23,'row_height'=>33,'row_count'=>22,'text'=>'湖北省武汉市青山区明教大楼25层403室企业战略策划部']; // 寄件人地址

        $template_data[] = ['type' => 'TEXT', 'start_x' => 10, 'start_y' => 10, 'font_size'=>23,'row_height'=>0,'row_count'=>0,'text'=>'集 快递']; // 快递图标,

        return ImageTemplateService::createFromPic('','1234567890',$back_file_path,$template_data);
    }
    /**
     * 打印自生成模板
     * @param $data
     * @return string
     */
    public static function printPending($data){
        //二维码 运单号+package_code+‘ ’+mark_destination
        $array=TemplateHelper::arrangeData($data);

        $width=800;
        $height=1440;

        $back_file_path='g2.jpg';
        $template_data=[
            ['type' => 'TEXT','start_x'=>480,'start_y'=>80,'font_size'=>46,'row_height'=>0,'row_count'=>0,'text'=>$array['eps_exp_type_name']], // 快递业务类型,$data['eps_exp_type_name']

            ['type' => 'QrCode','start_x'=>230,'start_y'=>100,'width'=>220,'text'=>$array['eps_order_code']], // 二维码

            ['type' => 'TEXT','start_x'=>90,'start_y'=>362,'font_size'=>23,'row_height'=>0,'row_count'=>0,'text'=>$array['consignee_info']], // 收件人姓名
            ['type' => 'TEXT','start_x'=>90,'start_y'=>400,'font_size'=>23,'row_height'=>33,'row_count'=>22,'text'=>$array['consignee_address']], // 收件人地址
            ['type' => 'TEXT','start_x'=>90,'start_y'=>526,'font_size'=>23,'row_height'=>0,'row_count'=>0,'text'=>$array['consignor_info']], // 寄件人姓名
            ['type' => 'TEXT','start_x'=>90,'start_y'=>564,'font_size'=>23,'row_height'=>33,'row_count'=>22,'text'=>$array['consignor_address']], // 寄件人地址

            ['type' => 'TEXT','start_x'=>10,'start_y'=>691,'font_size'=>23,'row_height'=>0,'row_count'=>0,'text'=>'付款方式:'.$array['eps_pay_type']], // 付款方式
            ['type' => 'TEXT','start_x'=>10,'start_y'=>727,'font_size'=>23,'row_height'=>0,'row_count'=>0,'text'=>'计算重量(kg):'.$array['eps_weight']], // 计算重量
            ['type' => 'TEXT','start_x'=>10,'start_y'=>763,'font_size'=>23,'row_height'=>0,'row_count'=>0,'text'=>'保价金额(元):'.$array['insured_value']], // 保价金额
            ['type' => 'TEXT','start_x'=>10,'start_y'=>799,'font_size'=>23,'row_height'=>0,'row_count'=>0,'text'=>'数量:'.$array['eps_quantity']], // 数量
            ['type' => 'TEXT','start_x'=>10,'start_y'=>835,'font_size'=>23,'row_height'=>0,'row_count'=>0,'text'=>'签回单:'.$array['eps_need_return_cname']], // 签回单
            ['type' => 'TEXT','start_x'=>10,'start_y'=>862,'font_size'=>14,'row_height'=>0,'row_count'=>0,'text'=>$array['print_time']], // 打印时间

            ['type' => 'TEXT','start_x'=>450,'start_y'=>720,'font_size'=>40,'row_height'=>55,'row_count'=>6,'text'=>'替代，需要重新下单'], // 签收人

            ['type' => 'QrCode','start_x'=>320,'start_y'=>887,'width'=>120,'text'=>$array['eps_order_code']], // 二维码

//            ['type' => 'BARCODE','start_x'=>320,'start_y'=>887,'width'=>440,'height'=>120], // 条形码,

            ['type' => 'TEXT','start_x'=>90,'start_y'=>1051,'font_size'=>23,'row_height'=>0,'row_count'=>0,'text'=>$array['consignee_info']], // 收件人姓名
            ['type' => 'TEXT','start_x'=>90,'start_y'=>1089,'font_size'=>23,'row_height'=>33,'row_count'=>22,'text'=>$array['consignee_address']], // 收件人地址
            ['type' => 'TEXT','start_x'=>90,'start_y'=>1191,'font_size'=>23,'row_height'=>0,'row_count'=>0,'text'=>$array['consignor_info']], // 寄件人姓名
            ['type' => 'TEXT','start_x'=>90,'start_y'=>1229,'font_size'=>23,'row_height'=>33,'row_count'=>22,'text'=>$array['consignor_address']], // 寄件人地址

            ['type' => 'TEXT','start_x'=>760,'start_y'=>255,'font_size'=>23,'row_height'=>0,'row_count'=>0,'text'=>'P'], // 待重新下单打印标志

            ['type' => 'TEXT','start_x'=>10,'start_y'=>1332,'font_size'=>23,'row_height'=>0,'row_count'=>0,'text'=>'物品:'.$array['goods_name']], //物品
            ['type' => 'TEXT','start_x'=>10,'start_y'=>1378,'font_size'=>23,'row_height'=>33,'row_count'=>25,'text'=>$array['eps_remark']], // 留言
        ];

        return ImageTemplateService::createFromPic($array['eps_shipper_code'],$array['eps_order_code'],$back_file_path,$template_data,false);
    }
    /**
     * 通用两联
     * @param $data
     * @return string
     */
    public static function getGeneral2($data){
        $array=TemplateHelper::arrangeData($data);

        $width=800;
        $height=1440;

        $back_file_path='g2.jpg';
        $template_data=[
            ['type' => 'TEXT','start_x'=>480,'start_y'=>80,'font_size'=>46,'row_height'=>46,'row_count'=>0,'text'=>$array['eps_exp_type_name']], // 快递业务类型,$data['eps_exp_type_name']

            ['type' => 'BARCODE','start_x'=>160,'start_y'=>225,'width'=>480,'height'=>100,'data'=>$array['logistic_code']], // 条形码,

            ['type' => 'TEXT','start_x'=>90,'start_y'=>362,'font_size'=>23,'row_height'=>0,'row_count'=>0,'text'=>$array['consignee_info']], // 收件人姓名
            ['type' => 'TEXT','start_x'=>90,'start_y'=>526,'font_size'=>23,'row_height'=>0,'row_count'=>0,'text'=>$array['consignor_info']], // 寄件人姓名

            ['type' => 'TEXT','start_x'=>10,'start_y'=>691,'font_size'=>23,'row_height'=>0,'row_count'=>0,'text'=>'付款方式:'.$array['eps_pay_type']], // 付款方式
            ['type' => 'TEXT','start_x'=>10,'start_y'=>727,'font_size'=>23,'row_height'=>0,'row_count'=>0,'text'=>'计算重量(kg):'.$array['eps_weight']], // 计算重量
            ['type' => 'TEXT','start_x'=>10,'start_y'=>763,'font_size'=>23,'row_height'=>0,'row_count'=>0,'text'=>'保价金额(元):'.$array['insured_value']], // 保价金额
            ['type' => 'TEXT','start_x'=>10,'start_y'=>799,'font_size'=>23,'row_height'=>0,'row_count'=>0,'text'=>'数量:'.$array['eps_quantity']], // 数量
            ['type' => 'TEXT','start_x'=>10,'start_y'=>835,'font_size'=>23,'row_height'=>0,'row_count'=>0,'text'=>'签回单:'.$array['eps_need_return_cname']], // 签回单
            ['type' => 'TEXT','start_x'=>10,'start_y'=>862,'font_size'=>14,'row_height'=>0,'row_count'=>0,'text'=>$array['print_time']], // 打印时间

            ['type' => 'TEXT','start_x'=>450,'start_y'=>691,'font_size'=>23,'row_height'=>0,'row_count'=>0,'text'=>'签收人:'], // 签收人
            ['type' => 'TEXT','start_x'=>450,'start_y'=>737,'font_size'=>23,'row_height'=>0,'row_count'=>0,'text'=>'签收时间:'], // 签收时间

            ['type' => 'BARCODE','start_x'=>320,'start_y'=>887,'width'=>440,'height'=>120,'data'=>$array['logistic_code']], // 条形码,

            ['type' => 'TEXT','start_x'=>90,'start_y'=>1051,'font_size'=>23,'row_height'=>0,'row_count'=>0,'text'=>$array['consignee_info_co']], // 收件人姓名
            ['type' => 'TEXT','start_x'=>90,'start_y'=>1089,'font_size'=>23,'row_height'=>33,'row_count'=>22,'text'=>$array['consignee_address']], // 收件人地址
            ['type' => 'TEXT','start_x'=>90,'start_y'=>1191,'font_size'=>23,'row_height'=>0,'row_count'=>0,'text'=>$array['consignor_info_co']], // 寄件人姓名
            ['type' => 'TEXT','start_x'=>90,'start_y'=>1229,'font_size'=>23,'row_height'=>33,'row_count'=>22,'text'=>$array['consignor_address']], // 寄件人地址


            ['type' => 'TEXT','start_x'=>10,'start_y'=>1378,'font_size'=>23,'row_height'=>33,'row_count'=>25,'text'=>$array['eps_remark']], // 留言
        ];
        $num_array=countStringNumber($array['mark_code']);
        $mark_start_x=($width-$num_array['ch']*58-$num_array['al']*48)/2;
        $template_data[] =['type' => 'TEXT','start_x'=>$mark_start_x,'start_y'=>187,'font_size'=>58,'row_height'=>0,'row_count'=>0,'text'=>$array['mark_code']]; // 大头笔
        //公司名
        if($data['receiver_company']){
            $co_startx=90+mb_strwidth($array['consignee_info'])*18;
            $template_data[] =['type' => 'TEXT','start_x'=>$co_startx,'start_y'=>362,'font_size'=>18,'row_height'=>0,'row_count'=>0,'text'=>$array['receiver_company']]; // 收件人公司
        }
        $template_data[] =['type' => 'TEXT','start_x'=>90,'start_y'=>400,'font_size'=>23,'row_height'=>33,'row_count'=>22,'text'=>$array['consignee_address']]; // 收件人地址

        if($data['sender_company']){
            $co_startx=90+mb_strwidth($array['consignor_info'])*18;
            $template_data[] =['type' => 'TEXT','start_x'=>$co_startx,'start_y'=>526,'font_size'=>18,'row_height'=>0,'row_count'=>0,'text'=>$array['sender_company']]; // 寄件人公司
        }
        $template_data[] =['type' => 'TEXT','start_x'=>90,'start_y'=>564,'font_size'=>23,'row_height'=>33,'row_count'=>22,'text'=>$array['consignor_address']]; // 寄件人地址

        if($array['eps_logo']) {
            $template_data[] = ['type' => 'IMAGE', 'start_x' => 10, 'start_y' => 10, 'width' => 400, 'height' => 90]; // 快递图标,
            $template_data[] = ['type' => 'IMAGE', 'start_x' => 10, 'start_y' => 887, 'width' => 280, 'height' => 120]; // 快递图标,
        }
        if(isset($array['reprint'])){
            $template_data[] =['type' => 'TEXT','start_x'=>760,'start_y'=>255,'font_size'=>23,'row_height'=>0,'row_count'=>0,'text'=>'R']; // 重新打印标志
        }
        if(isset($array['sequence_number'])){
            $template_data[] =['type' => 'TEXT','start_x'=>15,'start_y'=>295,'font_size'=>14,'row_height'=>0,'row_count'=>0,'text'=>$array['sequence_number']]; // 多件打印时的流水号
        }
        $goods_msg=$array['eps_shipper_code']=='EMS'?'内件:':'物品:';
        $template_data[] =['type' => 'TEXT','start_x'=>10,'start_y'=>1332,'font_size'=>23,'row_height'=>0,'row_count'=>0,'text'=>$goods_msg.$array['goods_name']]; //物品

        return ImageTemplateService::createFromPic($array['eps_shipper_code'],$array['logistic_code'],$back_file_path,$template_data);
    }

    /**
     * 打印图标，图标在左侧
     * @param $data
     * @return string
     */
    public static function getEMSLeft($data){
        $array=TemplateHelper::arrangeData($data);

        $width=800;
        $height=1440;

        $back_file_path='g2.jpg';
        $template_data=[
            ['type' => 'TEXT','start_x'=>480,'start_y'=>80,'font_size'=>46,'row_height'=>0,'row_count'=>0,'text'=>$array['eps_exp_type_name']], // 快递业务类型,$data['eps_exp_type_name']

            ['type' => 'BARCODE','start_x'=>160,'start_y'=>225,'width'=>480,'height'=>100,'data'=>$array['logistic_code']], // 条形码,

            ['type' => 'TEXT','start_x'=>90,'start_y'=>362,'font_size'=>23,'row_height'=>0,'row_count'=>0,'text'=>$array['consignee_info']], // 收件人姓名
            ['type' => 'TEXT','start_x'=>90,'start_y'=>526,'font_size'=>23,'row_height'=>0,'row_count'=>0,'text'=>$array['consignor_info']], // 寄件人姓名

            ['type' => 'TEXT','start_x'=>10,'start_y'=>691,'font_size'=>23,'row_height'=>0,'row_count'=>0,'text'=>'付款方式:'.$array['eps_pay_type']], // 付款方式
            ['type' => 'TEXT','start_x'=>10,'start_y'=>727,'font_size'=>23,'row_height'=>0,'row_count'=>0,'text'=>'计算重量(kg):'.$array['eps_weight']], // 计算重量
            ['type' => 'TEXT','start_x'=>10,'start_y'=>763,'font_size'=>23,'row_height'=>0,'row_count'=>0,'text'=>'保价金额(元):'.$array['insured_value']], // 保价金额
            ['type' => 'TEXT','start_x'=>10,'start_y'=>799,'font_size'=>23,'row_height'=>0,'row_count'=>0,'text'=>'数量:'.$array['eps_quantity']], // 数量
            ['type' => 'TEXT','start_x'=>10,'start_y'=>835,'font_size'=>23,'row_height'=>0,'row_count'=>0,'text'=>'签回单:'.$array['eps_need_return_cname']], // 签回单
            ['type' => 'TEXT','start_x'=>10,'start_y'=>862,'font_size'=>14,'row_height'=>0,'row_count'=>0,'text'=>$array['print_time']], // 打印时间

            ['type' => 'TEXT','start_x'=>450,'start_y'=>691,'font_size'=>23,'row_height'=>0,'row_count'=>0,'text'=>'签收人:'], // 签收人
            ['type' => 'TEXT','start_x'=>450,'start_y'=>737,'font_size'=>23,'row_height'=>0,'row_count'=>0,'text'=>'签收时间:'], // 签收时间

            ['type' => 'BARCODE','start_x'=>320,'start_y'=>887,'width'=>440,'height'=>120,'data'=>$array['logistic_code']], // 条形码,

            ['type' => 'TEXT','start_x'=>90,'start_y'=>1051,'font_size'=>23,'row_height'=>0,'row_count'=>0,'text'=>$array['consignee_info_co']], // 收件人姓名
            ['type' => 'TEXT','start_x'=>90,'start_y'=>1089,'font_size'=>23,'row_height'=>33,'row_count'=>22,'text'=>$array['consignee_address']], // 收件人地址
            ['type' => 'TEXT','start_x'=>90,'start_y'=>1191,'font_size'=>23,'row_height'=>0,'row_count'=>0,'text'=>$array['consignor_info_co']], // 寄件人姓名
            ['type' => 'TEXT','start_x'=>90,'start_y'=>1229,'font_size'=>23,'row_height'=>33,'row_count'=>22,'text'=>$array['consignor_address']], // 寄件人地址

            ['type' => 'TEXT','start_x'=>10,'start_y'=>1332,'font_size'=>23,'row_height'=>0,'row_count'=>0,'text'=>'内件:'.$array['goods_name']], //物品
            ['type' => 'TEXT','start_x'=>10,'start_y'=>1378,'font_size'=>23,'row_height'=>33,'row_count'=>25,'text'=>$array['eps_remark']], // 留言
        ];
        $num_array=countStringNumber($array['mark_code']);
        $mark_start_x=($width-$num_array['ch']*58-$num_array['al']*48)/2;
        $template_data[] =['type' => 'TEXT','start_x'=>$mark_start_x,'start_y'=>187,'font_size'=>58,'row_height'=>0,'row_count'=>0,'text'=>$array['mark_code']]; // 大头笔
        //公司名
        if($data['receiver_company']){
            $co_startx=90+mb_strwidth($array['consignee_info'])*18;
            $template_data[] =['type' => 'TEXT','start_x'=>$co_startx,'start_y'=>362,'font_size'=>18,'row_height'=>0,'row_count'=>0,'text'=>$array['receiver_company']]; // 收件人公司
        }
        $template_data[] =['type' => 'TEXT','start_x'=>90,'start_y'=>400,'font_size'=>23,'row_height'=>33,'row_count'=>22,'text'=>$array['consignee_address']]; // 收件人地址

        if($data['sender_company']){
            $co_startx=90+mb_strwidth($array['consignor_info'])*18;
            $template_data[] =['type' => 'TEXT','start_x'=>$co_startx,'start_y'=>526,'font_size'=>18,'row_height'=>0,'row_count'=>0,'text'=>$array['sender_company']]; // 寄件人公司
        }
        $template_data[] =['type' => 'TEXT','start_x'=>90,'start_y'=>564,'font_size'=>23,'row_height'=>33,'row_count'=>22,'text'=>$array['consignor_address']]; // 寄件人地址

        if($array['eps_logo']) {
            $template_data[] = ['type' => 'IMAGE', 'start_x' => 10, 'start_y' => 887, 'width' => 280, 'height' => 120]; // 快递图标,
        }
        if(isset($array['reprint'])){
            $template_data[] =['type' => 'TEXT','start_x'=>760,'start_y'=>255,'font_size'=>23,'row_height'=>0,'row_count'=>0,'text'=>'R']; // 重新打印标志
        }
        if(isset($array['sequence_number'])){
            $template_data[] =['type' => 'TEXT','start_x'=>15,'start_y'=>295,'font_size'=>14,'row_height'=>0,'row_count'=>0,'text'=>$array['sequence_number']]; // 多件打印时的流水号
        }

        return ImageTemplateService::createFromPic($array['eps_shipper_code'],$array['logistic_code'],$back_file_path,$template_data);
    }

    /**
     * 打印图标，图标在右侧
     * @param $data
     * @return string
     */
    public static function getEMSRight($data){
        $array=TemplateHelper::arrangeData($data);

        $width=800;
        $height=1440;

        $back_file_path='g2.jpg';
        $template_data=[
            ['type' => 'TEXT','start_x'=>480,'start_y'=>80,'font_size'=>46,'row_height'=>0,'row_count'=>0,'text'=>$array['eps_exp_type_name']], // 快递业务类型,$data['eps_exp_type_name']

            ['type' => 'BARCODE','start_x'=>160,'start_y'=>225,'width'=>480,'height'=>100,'data'=>$array['logistic_code']], // 条形码,

            ['type' => 'TEXT','start_x'=>90,'start_y'=>362,'font_size'=>23,'row_height'=>0,'row_count'=>0,'text'=>$array['consignee_info']], // 收件人姓名
            ['type' => 'TEXT','start_x'=>90,'start_y'=>526,'font_size'=>23,'row_height'=>0,'row_count'=>0,'text'=>$array['consignor_info']], // 寄件人姓名

            ['type' => 'TEXT','start_x'=>10,'start_y'=>691,'font_size'=>23,'row_height'=>0,'row_count'=>0,'text'=>'付款方式:'.$array['eps_pay_type']], // 付款方式
            ['type' => 'TEXT','start_x'=>10,'start_y'=>727,'font_size'=>23,'row_height'=>0,'row_count'=>0,'text'=>'计算重量(kg):'.$array['eps_weight']], // 计算重量
            ['type' => 'TEXT','start_x'=>10,'start_y'=>763,'font_size'=>23,'row_height'=>0,'row_count'=>0,'text'=>'保价金额(元):'.$array['insured_value']], // 保价金额
            ['type' => 'TEXT','start_x'=>10,'start_y'=>799,'font_size'=>23,'row_height'=>0,'row_count'=>0,'text'=>'数量:'.$array['eps_quantity']], // 数量
            ['type' => 'TEXT','start_x'=>10,'start_y'=>835,'font_size'=>23,'row_height'=>0,'row_count'=>0,'text'=>'签回单:'.$array['eps_need_return_cname']], // 签回单
            ['type' => 'TEXT','start_x'=>10,'start_y'=>862,'font_size'=>14,'row_height'=>0,'row_count'=>0,'text'=>$array['print_time']], // 打印时间

            ['type' => 'TEXT','start_x'=>450,'start_y'=>691,'font_size'=>23,'row_height'=>0,'row_count'=>0,'text'=>'签收人:'], // 签收人
            ['type' => 'TEXT','start_x'=>450,'start_y'=>737,'font_size'=>23,'row_height'=>0,'row_count'=>0,'text'=>'签收时间:'], // 签收时间

            ['type' => 'BARCODE','start_x'=>320,'start_y'=>887,'width'=>440,'height'=>120,'data'=>$array['logistic_code']], // 条形码,

            ['type' => 'TEXT','start_x'=>90,'start_y'=>1051,'font_size'=>23,'row_height'=>0,'row_count'=>0,'text'=>$array['consignee_info_co']], // 收件人姓名
            ['type' => 'TEXT','start_x'=>90,'start_y'=>1089,'font_size'=>23,'row_height'=>33,'row_count'=>22,'text'=>$array['consignee_address']], // 收件人地址
            ['type' => 'TEXT','start_x'=>90,'start_y'=>1191,'font_size'=>23,'row_height'=>0,'row_count'=>0,'text'=>$array['consignor_info_co']], // 寄件人姓名
            ['type' => 'TEXT','start_x'=>90,'start_y'=>1229,'font_size'=>23,'row_height'=>33,'row_count'=>22,'text'=>$array['consignor_address']], // 寄件人地址

            ['type' => 'TEXT','start_x'=>10,'start_y'=>1332,'font_size'=>23,'row_height'=>0,'row_count'=>0,'text'=>'内件:'.$array['goods_name']], //物品
            ['type' => 'TEXT','start_x'=>10,'start_y'=>1378,'font_size'=>23,'row_height'=>33,'row_count'=>25,'text'=>$array['eps_remark']], // 留言
        ];
        $num_array=countStringNumber($array['mark_code']);
        $mark_start_x=($width-$num_array['ch']*58-$num_array['al']*48)/2;
        $template_data[] =['type' => 'TEXT','start_x'=>$mark_start_x,'start_y'=>187,'font_size'=>58,'row_height'=>0,'row_count'=>0,'text'=>$array['mark_code']]; // 大头笔
        //公司名
        if($data['receiver_company']){
            $co_startx=90+mb_strwidth($array['consignee_info'])*18;
            $template_data[] =['type' => 'TEXT','start_x'=>$co_startx,'start_y'=>362,'font_size'=>18,'row_height'=>0,'row_count'=>0,'text'=>$array['receiver_company']]; // 收件人公司
        }
        $template_data[] =['type' => 'TEXT','start_x'=>90,'start_y'=>400,'font_size'=>23,'row_height'=>33,'row_count'=>22,'text'=>$array['consignee_address']]; // 收件人地址

        if($data['sender_company']){
            $co_startx=90+mb_strwidth($array['consignor_info'])*18;
            $template_data[] =['type' => 'TEXT','start_x'=>$co_startx,'start_y'=>526,'font_size'=>18,'row_height'=>0,'row_count'=>0,'text'=>$array['sender_company']]; // 寄件人公司
        }
        $template_data[] =['type' => 'TEXT','start_x'=>90,'start_y'=>564,'font_size'=>23,'row_height'=>33,'row_count'=>22,'text'=>$array['consignor_address']]; // 寄件人地址

//        if($array['eps_logo']) {
//            $template_data[] = ['type' => 'IMAGE', 'start_x' => 10, 'start_y' => 887, 'width' => 280, 'height' => 120]; // 快递图标,
//        }
        if(isset($array['reprint'])){
            $template_data[] =['type' => 'TEXT','start_x'=>760,'start_y'=>255,'font_size'=>23,'row_height'=>0,'row_count'=>0,'text'=>'R']; // 重新打印标志
        }
        if(isset($array['sequence_number'])){
            $template_data[] =['type' => 'TEXT','start_x'=>15,'start_y'=>295,'font_size'=>14,'row_height'=>0,'row_count'=>0,'text'=>$array['sequence_number']]; // 多件打印时的流水号
        }

        return ImageTemplateService::createFromPic($array['eps_shipper_code'],$array['logistic_code'],$back_file_path,$template_data);
    }

    /**
     * 180三联模板
     * @param array $data
     * @return string
     */
    public static  function getG180E3($data=[]){

        $array=TemplateHelper::arrangeData($data);

        $width=800;
        $height=1440;

        $back_file_path='g2e3.jpg';
        $template_data=[
            ['type' => 'TEXT','start_x'=>480,'start_y'=>80,'font_size'=>46,'row_height'=>0,'row_count'=>0,'text'=>$array['eps_exp_type_name']], // 快递业务类型,$data['eps_exp_type_name']
            //['type' => 'TEXT','start_x'=>10,'start_y'=>190,'font_size'=>55,'row_height'=>0,'row_count'=>0,'style'=>'bold','text'=>$array['mark_code']],//区
            ['type' => 'TEXT','start_x'=>102,'start_y'=>271,'font_size'=>45,'row_height'=>0,'row_count'=>0,'text'=>$array['package_name']],
            //['type' => 'BARCODE','start_x'=>570,'start_y'=>225,'width'=>200,'height'=>80,'data'=>$array['sorting_code']],  //分拣码条形码
            //['type' => 'TEXT','start_x'=>120,'start_y'=>331,'font_size'=>140,'row_height'=>0,'row_count'=>0,'color'=>'gray','style'=>'bold','text'=>$array['destinatio_code']],//目的编码
            ['type' => 'BARCODE','start_x'=>100,'start_y'=>530,'width'=>600,'height'=>148,'data'=>$array['logistic_code']], // 条形码,

            ['type' => 'TEXT','start_x'=>102,'start_y'=>335,'font_size'=>23,'row_height'=>0,'row_count'=>0,'text'=>$array['consignee_info']], // 收件人姓名
//            ['type' => 'TEXT','start_x'=>381,'start_y'=>345,'font_size'=>30,'row_height'=>0,'row_count'=>0,'text'=>$array['receiver_mobile']],
            ['type' => 'TEXT','start_x'=>102,'start_y'=>368,'font_size'=>23,'row_height'=>30,'row_count'=>22,'text'=>$array['consignee_address']], // 收件人地址
            ['type' => 'TEXT','start_x'=>102,'start_y'=>443,'font_size'=>18,'row_height'=>0,'row_count'=>0,'text'=>$array['consignor_info']], // 寄件人姓名
//            ['type' => 'TEXT','start_x'=>200,'start_y'=>448,'font_size'=>18,'row_height'=>0,'row_count'=>0,'text'=>$array['sender_mobile']],//寄件人电话
            ['type' => 'TEXT','start_x'=>102,'start_y'=>470,'font_size'=>18,'row_height'=>23,'row_count'=>30,'text'=>$array['consignor_address']], // 寄件人地址

            ['type' => 'TEXT','start_x'=>12,'start_y'=>709,'font_size'=>21,'row_height'=>0,'row_count'=>0,'text'=>'付款方式:'.$array['eps_pay_type']], // 付款方式
            ['type' => 'TEXT','start_x'=>12,'start_y'=>738,'font_size'=>21,'row_height'=>0,'row_count'=>0,'text'=>'计算重量(kg):'.$array['eps_weight']], // 计算重量
            ['type' => 'TEXT','start_x'=>12,'start_y'=>767,'font_size'=>21,'row_height'=>0,'row_count'=>0,'text'=>'保价金额(元):'.$array['insured_value']], // 保价金额
            ['type' => 'TEXT','start_x'=>12,'start_y'=>796,'font_size'=>21,'row_height'=>0,'row_count'=>0,'text'=>'数量:'.$array['eps_quantity']], // 数量
            ['type' => 'TEXT','start_x'=>12,'start_y'=>825,'font_size'=>21,'row_height'=>0,'row_count'=>0,'text'=>'签回单:'.$array['eps_need_return_cname']], // 签回单
            ['type' => 'TEXT','start_x'=>12,'start_y'=>855,'font_size'=>14,'row_height'=>0,'row_count'=>0,'text'=>$array['print_time']], // 打印时间

            ['type' => 'BARCODE','start_x'=>360,'start_y'=>887,'width'=>400,'height'=>60,'data'=>$array['logistic_code']], // 条形码,
            ['type' => 'TEXT','start_x'=>50,'start_y'=>980,'font_size'=>19,'row_height'=>24,'row_count'=>20,'text'=>$array['consignee_info_co'].$array['consignee_address']], // 收件人地址
            ['type' => 'TEXT','start_x'=>50,'start_y'=>1057,'font_size'=>19,'row_height'=>24,'row_count'=>20,'text'=>$array['consignor_info_co'].$array['consignor_address']], // 寄件人姓名
            ['type' => 'TEXT','start_x'=>580,'start_y'=>980,'font_size'=>16,'row_height'=>22,'row_count'=>11,'text'=>$array['goods_name'].$array['eps_remark']], //物品

//            ['type' => 'TEXT','start_x'=>10,'start_y'=>1110,'font_size'=>23,'row_height'=>0,'row_count'=>0,'text'=>$array['eps_name']], // 快递公司
            ['type' => 'BARCODE','start_x'=>320,'start_y'=>1115,'width'=>380,'height'=>50,'data'=>$array['logistic_code']], // 条形码,
            ['type' => 'TEXT','start_x'=>50,'start_y'=>1191,'font_size'=>19,'row_height'=>26,'row_count'=>27,'text'=>$array['consignee_info_co'].$array['consignee_address']], // 收件人
            ['type' => 'TEXT','start_x'=>50,'start_y'=>1260,'font_size'=>19,'row_height'=>26,'row_count'=>27,'text'=>$array['consignor_info_co'].$array['consignor_address']], // 寄件人
            ['type' => 'TEXT','start_x'=>10,'start_y'=>1345,'font_size'=>19,'row_height'=>26,'row_count'=>29,'text'=>$array['goods_name'].$array['eps_remark']], //物品
        ];
       /* $num_array=countStringNumber($array['mark_code']);
        $mark_start_x=($width-$num_array['ch']*58-$num_array['al']*48)/2;
        $template_data[] =['type' => 'TEXT','start_x'=>$mark_start_x,'start_y'=>187,'font_size'=>58,'row_height'=>0,'row_count'=>0,'text'=>$array['mark_code']]; // 大头笔*/
        if(self::sstrlen($array['mark_code'])>14){
            $template_data[]=['type' => 'TEXT','start_x'=>10,'start_y'=>187,'font_size'=>50,'row_height'=>0,'row_count'=>0,'style'=>'bold','text'=>$array['mark_code']];//区
        }else{
            $template_data[]=['type' => 'TEXT','start_x'=>10,'start_y'=>187,'font_size'=>60,'row_height'=>0,'row_count'=>0,'style'=>'bold','text'=>$array['mark_code']];//区
        }
        //公司名
        if($data['receiver_company']){
            $co_startx=90+mb_strwidth($array['consignee_info'])*18;
            $template_data[] =['type' => 'TEXT','start_x'=>$co_startx,'start_y'=>335,'font_size'=>18,'row_height'=>0,'row_count'=>0,'text'=>$array['receiver_company']]; // 收件人公司
        }

        if($data['sender_company']){
            $co_startx=90+mb_strwidth($array['consignor_info'])*18;
            $template_data[] =['type' => 'TEXT','start_x'=>$co_startx,'start_y'=>443,'font_size'=>18,'row_height'=>0,'row_count'=>0,'text'=>$array['sender_company']]; // 寄件人公司
        }

        if($array['eps_logo']) {
            $template_data[] = ['type' => 'IMAGE', 'start_x' => 10, 'start_y' => 10, 'width' => 400, 'height' => 90]; // 快递图标,
            $template_data[] = ['type' => 'IMAGE', 'start_x' => 10, 'start_y' => 887, 'width' => 280, 'height' => 60]; // 快递图标,
            $template_data[] = ['type' => 'IMAGE', 'start_x' => 10, 'start_y' => 1115, 'width' => 280, 'height' => 50]; // 快递图标,
        }
        if(isset($array['reprint'])){
            $template_data[] =['type' => 'TEXT','start_x'=>760,'start_y'=>255,'font_size'=>23,'row_height'=>0,'row_count'=>0,'text'=>'R']; // 重新打印标志
        }
        if(isset($array['sequence_number'])){
            $template_data[] =['type' => 'TEXT','start_x'=>15,'start_y'=>295,'font_size'=>14,'row_height'=>0,'row_count'=>0,'text'=>$array['sequence_number']]; // 多件打印时的流水号
        }
//        $goods_msg=$array['eps_shipper_code']=='EMS'?'内件:':'物品:';
//        $template_data[] =['type' => 'TEXT','start_x'=>10,'start_y'=>1332,'font_size'=>23,'row_height'=>0,'row_count'=>0,'text'=>$goods_msg.$array['goods_name']]; //物品
        if($array['package_code']){
            $template_data[]=['type' => 'BARCODE','start_x'=>488,'start_y'=>213,'width'=>250,'height'=>80,'data'=>$array['package_code']];//集包地编码圆通
        }
        return ImageTemplateService::createFromPic($array['eps_shipper_code'],$array['logistic_code'],$back_file_path,$template_data);

    }

    /**
     * 180三联模板无图标
     * @param array $data
     * @return string
     */
    public function getG180E3W($data=[]){
        $data['eps_logo']=0;
        return ImageTemplateService::getG180E3($data);
    }
    /**
     * 二联打印三联模板
     * @param array $data
     * @return string
     */
    public static  function getGeneral23($data=[]){

        $array=TemplateHelper::arrangeData($data);

        $back_file_path='g23.jpg';
        $template_data=[
            ['type' => 'TEXT','start_x'=>480,'start_y'=>60,'font_size'=>46,'row_height'=>0,'row_count'=>0,'text'=>$array['eps_exp_type_name']], // 快递业务类型,$data['eps_exp_type_name']
            ['type' => 'TEXT','start_x'=>160,'start_y'=>187,'font_size'=>58,'row_height'=>0,'row_count'=>0,'text'=>$array['mark_code']], // 大头笔
            ['type' => 'BARCODE','start_x'=>160,'start_y'=>200,'width'=>480,'height'=>93,'data'=>$array['logistic_code']], // 条形码,

            ['type' => 'TEXT','start_x'=>100,'start_y'=>325,'font_size'=>18,'row_height'=>0,'row_count'=>0,'text'=>$array['consignee_info_co']], // 收件人姓名
            ['type' => 'TEXT','start_x'=>100,'start_y'=>355,'font_size'=>18,'row_height'=>33,'row_count'=>22,'text'=>$array['consignee_address']], // 收件人地址
            ['type' => 'TEXT','start_x'=>100,'start_y'=>425,'font_size'=>18,'row_height'=>0,'row_count'=>0,'text'=>$array['consignor_info_co']], // 寄件人姓名
            ['type' => 'TEXT','start_x'=>100,'start_y'=>455,'font_size'=>18,'row_height'=>33,'row_count'=>22,'text'=>$array['consignor_address']], // 寄件人地址

            ['type' => 'TEXT','start_x'=>10,'start_y'=>520,'font_size'=>16,'row_height'=>0,'row_count'=>0,'text'=>'付款方式:'.$array['eps_pay_type']], // 付款方式
            ['type' => 'TEXT','start_x'=>10,'start_y'=>550,'font_size'=>16,'row_height'=>0,'row_count'=>0,'text'=>'计算重量(kg):'.$array['eps_weight']], // 计算重量
            ['type' => 'TEXT','start_x'=>10,'start_y'=>580,'font_size'=>16,'row_height'=>0,'row_count'=>0,'text'=>'保价金额(元):'.$array['insured_value']], // 保价金额
            ['type' => 'TEXT','start_x'=>270,'start_y'=>550,'font_size'=>16,'row_height'=>0,'row_count'=>0,'text'=>'数量:'.$array['eps_quantity']], // 数量
            ['type' => 'TEXT','start_x'=>10,'start_y'=>610,'font_size'=>16,'row_height'=>0,'row_count'=>0,'text'=>'签回单:'.$array['eps_need_return_cname']], // 签回单
            //['type' => 'TEXT','start_x'=>260,'start_y'=>625,'font_size'=>18,'row_height'=>0,'row_count'=>0,'text'=>$array['order_time']], // 下单时间
            //['type' => 'TEXT','start_x'=>450,'start_y'=>691,'font_size'=>18,'row_height'=>0,'row_count'=>0,'text'=>'签收人:'], // 签收人
            ['type' => 'TEXT','start_x'=>270,'start_y'=>610,'font_size'=>12,'row_height'=>0,'row_count'=>0,'text'=>$array['print_time']], // 打印时间

            ['type' => 'BARCODE','start_x'=>320,'start_y'=>622,'width'=>440,'height'=>93,'data'=>$array['logistic_code']], // 条形码,
            ['type' => 'BARCODE','start_x'=>320,'start_y'=>884,'width'=>440,'height'=>93,'data'=>$array['logistic_code']], // 条形码,

            ['type' => 'TEXT','start_x'=>100,'start_y'=>742,'font_size'=>18,'row_height'=>0,'row_count'=>0,'text'=>$array['consignee_info_co']], // 收件人姓名
            ['type' => 'TEXT','start_x'=>100,'start_y'=>772,'font_size'=>18,'row_height'=>33,'row_count'=>22,'text'=>$array['consignee_address']], // 收件人地址
            ['type' => 'TEXT','start_x'=>100,'start_y'=>822,'font_size'=>18,'row_height'=>0,'row_count'=>0,'text'=>$array['consignor_info_co']], // 寄件人姓名
            ['type' => 'TEXT','start_x'=>100,'start_y'=>852,'font_size'=>18,'row_height'=>33,'row_count'=>22,'text'=>$array['consignor_address']], // 寄件人地址

            ['type' => 'TEXT','start_x'=>100,'start_y'=>1009,'font_size'=>18,'row_height'=>0,'row_count'=>0,'text'=>$array['consignee_info_co']], // 收件人姓名
            ['type' => 'TEXT','start_x'=>100,'start_y'=>1039,'font_size'=>18,'row_height'=>33,'row_count'=>22,'text'=>$array['consignee_address']], // 收件人地址
            ['type' => 'TEXT','start_x'=>100,'start_y'=>1155,'font_size'=>18,'row_height'=>0,'row_count'=>0,'text'=>$array['consignor_info_co']], // 寄件人姓名
            ['type' => 'TEXT','start_x'=>100,'start_y'=>1185,'font_size'=>18,'row_height'=>33,'row_count'=>22,'text'=>$array['consignor_address']], // 寄件人地址

            ['type' => 'TEXT','start_x'=>70,'start_y'=>1289,'font_size'=>16,'row_height'=>0,'row_count'=>0,'text'=>$array['goods_name']], //物品
            ['type' => 'TEXT','start_x'=>10,'start_y'=>1317,'font_size'=>18,'row_height'=>33,'row_count'=>25,'text'=>$array['eps_remark']], // 留言
        ];
        if($array['eps_logo']) {
            $template_data[] = ['type' => 'IMAGE', 'start_x' => 10, 'start_y' => 5, 'width' => 400, 'height' => 90]; // 快递图标,
            $template_data[] = ['type' => 'IMAGE', 'start_x' => 10, 'start_y' => 622, 'width' => 280, 'height' => 93]; // 快递图标,
            $template_data[] = ['type' => 'IMAGE', 'start_x' => 10, 'start_y' => 884, 'width' => 280, 'height' => 93]; // 快递图标,
        }

        if(isset($array['reprint'])){
            $template_data[] =['type' => 'TEXT','start_x'=>760,'start_y'=>255,'font_size'=>23,'row_height'=>0,'row_count'=>0,'text'=>'R']; // 重新打印标志
        }

        return ImageTemplateService::createFromPic($array['eps_shipper_code'],$array['logistic_code'],$back_file_path,$template_data);

    }

    /**
     * 三联模板
     * @param array $data
     * @return string
     */
    public static  function getGeneral3($data=[]){

        $array=TemplateHelper::arrangeData($data);

        $back_file_path='g3.jpg';
        $template_data=[
            ['type' => 'TEXT','start_x'=>480,'start_y'=>80,'font_size'=>46,'row_height'=>0,'row_count'=>0,'text'=>$array['eps_exp_type_name']], // 快递业务类型,$data['eps_exp_type_name']
            ['type' => 'TEXT','start_x'=>160,'start_y'=>187,'font_size'=>58,'row_height'=>0,'row_count'=>0,'text'=>$array['mark_code']], // 大头笔
            ['type' => 'BARCODE','start_x'=>160,'start_y'=>225,'width'=>480,'height'=>100,'data'=>$array['logistic_code']], // 条形码,

            ['type' => 'TEXT','start_x'=>90,'start_y'=>362,'font_size'=>23,'row_height'=>0,'row_count'=>0,'text'=>$array['consignee_info_co']], // 收件人姓名
            ['type' => 'TEXT','start_x'=>90,'start_y'=>400,'font_size'=>23,'row_height'=>33,'row_count'=>22,'text'=>$array['consignee_address']], // 收件人地址
            ['type' => 'TEXT','start_x'=>90,'start_y'=>526,'font_size'=>23,'row_height'=>0,'row_count'=>0,'text'=>$array['consignor_info_co']], // 寄件人姓名
            ['type' => 'TEXT','start_x'=>90,'start_y'=>564,'font_size'=>23,'row_height'=>33,'row_count'=>22,'text'=>$array['consignor_address']], // 寄件人地址

            ['type' => 'TEXT','start_x'=>10,'start_y'=>691,'font_size'=>23,'row_height'=>0,'row_count'=>0,'text'=>'付款方式:'.$array['eps_pay_type']], // 付款方式
            ['type' => 'TEXT','start_x'=>10,'start_y'=>727,'font_size'=>23,'row_height'=>0,'row_count'=>0,'text'=>'计算重量(kg):'.$array['eps_weight']], // 计算重量
            ['type' => 'TEXT','start_x'=>10,'start_y'=>763,'font_size'=>23,'row_height'=>0,'row_count'=>0,'text'=>'保价金额(元):'.$array['insured_value']], // 保价金额
            ['type' => 'TEXT','start_x'=>10,'start_y'=>799,'font_size'=>23,'row_height'=>0,'row_count'=>0,'text'=>'数量:'.$array['eps_quantity']], // 数量
            ['type' => 'TEXT','start_x'=>10,'start_y'=>835,'font_size'=>23,'row_height'=>0,'row_count'=>0,'text'=>'签回单:'.$array['eps_need_return_cname']], // 签回单
            ['type' => 'TEXT','start_x'=>260,'start_y'=>835,'font_size'=>23,'row_height'=>0,'row_count'=>0,'text'=>$array['order_time']], // 下单时间
            ['type' => 'TEXT','start_x'=>450,'start_y'=>691,'font_size'=>23,'row_height'=>0,'row_count'=>0,'text'=>'签收人:'], // 签收人
            ['type' => 'TEXT','start_x'=>10,'start_y'=>862,'font_size'=>14,'row_height'=>0,'row_count'=>0,'text'=>$array['print_time']], // 打印时间

            ['type' => 'BARCODE','start_x'=>320,'start_y'=>887,'width'=>440,'height'=>120,'data'=>$array['logistic_code']], // 条形码,

            ['type' => 'TEXT','start_x'=>90,'start_y'=>1051,'font_size'=>23,'row_height'=>0,'row_count'=>0,'text'=>$array['consignee_info_co']], // 收件人姓名
            ['type' => 'TEXT','start_x'=>90,'start_y'=>1089,'font_size'=>23,'row_height'=>33,'row_count'=>22,'text'=>$array['consignee_address']], // 收件人地址
            ['type' => 'TEXT','start_x'=>90,'start_y'=>1191,'font_size'=>23,'row_height'=>0,'row_count'=>0,'text'=>$array['consignor_info_co']], // 寄件人姓名
            ['type' => 'TEXT','start_x'=>90,'start_y'=>1229,'font_size'=>23,'row_height'=>33,'row_count'=>22,'text'=>$array['consignor_address']], // 寄件人地址

            ['type' => 'TEXT','start_x'=>10,'start_y'=>1332,'font_size'=>23,'row_height'=>0,'row_count'=>0,'text'=>'物品:'.$array['goods_name']], //物品
            ['type' => 'TEXT','start_x'=>10,'start_y'=>1378,'font_size'=>23,'row_height'=>33,'row_count'=>25,'text'=>$array['eps_remark']], // 留言

            ['type' => 'TEXT','start_x'=>90,'start_y'=>1468,'font_size'=>23,'row_height'=>0,'row_count'=>0,'text'=>$array['consignee_info_co']], // 收件人姓名
            ['type' => 'TEXT','start_x'=>90,'start_y'=>1506,'font_size'=>23,'row_height'=>33,'row_count'=>22,'text'=>$array['consignee_address']], // 收件人地址
            ['type' => 'TEXT','start_x'=>90,'start_y'=>1610,'font_size'=>23,'row_height'=>0,'row_count'=>0,'text'=>$array['consignor_info_co']], // 寄件人姓名
            ['type' => 'TEXT','start_x'=>90,'start_y'=>1648,'font_size'=>23,'row_height'=>33,'row_count'=>22,'text'=>$array['consignor_address']], // 寄件人地址
        ];
        if($array['eps_logo']) {
            $template_data[] = ['type' => 'IMAGE', 'start_x' => 10, 'start_y' => 10, 'width' => 400, 'height' => 90]; // 快递图标,
            $template_data[] = ['type' => 'IMAGE', 'start_x' => 10, 'start_y' => 887, 'width' => 280, 'height' => 120]; // 快递图标,
        }

        if(isset($array['reprint'])){
            $template_data[] =['type' => 'TEXT','start_x'=>760,'start_y'=>255,'font_size'=>23,'row_height'=>0,'row_count'=>0,'text'=>'R']; // 重新打印标志
        }

        return ImageTemplateService::createFromPic($array['eps_shipper_code'],$array['logistic_code'],$back_file_path,$template_data);

    }

    /**
     * EMS三联单
     * @param array $data
     * @return string
     */
    public static  function getEMS3($data=[]){

        $array=TemplateHelper::arrangeData($data);

        $back_file_path='ems3.jpg';
        $template_data=[
            ['type' => 'TEXT','start_x'=>480,'start_y'=>80,'font_size'=>46,'row_height'=>0,'row_count'=>0,'text'=>$array['eps_exp_type_name']], // 快递业务类型,$data['eps_exp_type_name']
            ['type' => 'TEXT','start_x'=>160,'start_y'=>180,'font_size'=>58,'row_height'=>0,'row_count'=>0,'text'=>$array['mark_code']], // 大头笔
            ['type' => 'BARCODE','start_x'=>160,'start_y'=>206,'width'=>480,'height'=>100,'data'=>$array['logistic_code']], // 条形码,

            ['type' => 'TEXT','start_x'=>90,'start_y'=>346,'font_size'=>23,'row_height'=>0,'row_count'=>0,'text'=>$array['consignee_info_co']], // 收件人姓名
            ['type' => 'TEXT','start_x'=>90,'start_y'=>380,'font_size'=>23,'row_height'=>33,'row_count'=>22,'text'=>$array['consignee_address']], // 收件人地址
            ['type' => 'TEXT','start_x'=>90,'start_y'=>493,'font_size'=>21,'row_height'=>0,'row_count'=>0,'text'=>$array['consignor_info_co']], // 寄件人姓名
            ['type' => 'TEXT','start_x'=>90,'start_y'=>527,'font_size'=>21,'row_height'=>33,'row_count'=>24,'text'=>$array['consignor_address']], // 寄件人地址

            ['type' => 'TEXT','start_x'=>10,'start_y'=>615,'font_size'=>17,'row_height'=>0,'row_count'=>0,'text'=>'付款方式:'.$array['eps_pay_type']], // 付款方式
            ['type' => 'TEXT','start_x'=>10,'start_y'=>640,'font_size'=>17,'row_height'=>0,'row_count'=>0,'text'=>'计算重量(kg):'.$array['eps_weight']], // 计算重量
            ['type' => 'TEXT','start_x'=>270,'start_y'=>640,'font_size'=>17,'row_height'=>0,'row_count'=>0,'text'=>'保价金额(元):'.$array['insured_value']], // 保价金额
            ['type' => 'TEXT','start_x'=>10,'start_y'=>665,'font_size'=>17,'row_height'=>0,'row_count'=>0,'text'=>'数量:'.$array['eps_quantity']], // 数量
            ['type' => 'TEXT','start_x'=>10,'start_y'=>695,'font_size'=>17,'row_height'=>0,'row_count'=>0,'text'=>'签回单:'.$array['eps_need_return_cname']], // 签回单
            ['type' => 'TEXT','start_x'=>290,'start_y'=>695,'font_size'=>17,'row_height'=>0,'row_count'=>0,'text'=>$array['order_time']], // 下单时间
//            ['type' => 'TEXT','start_x'=>450,'start_y'=>597,'font_size'=>21,'row_height'=>0,'row_count'=>0,'text'=>'签收人:'], // 签收人
            ['type' => 'TEXT','start_x'=>10,'start_y'=>720,'font_size'=>14,'row_height'=>0,'row_count'=>0,'text'=>'打印时间:'.$array['print_time']], // 打印时间

            ['type' => 'BARCODE','start_x'=>320,'start_y'=>733,'width'=>440,'height'=>120,'data'=>$array['logistic_code']], // 条形码,

            ['type' => 'TEXT','start_x'=>90,'start_y'=>892,'font_size'=>23,'row_height'=>0,'row_count'=>0,'text'=>$array['consignee_info_co']], // 收件人姓名
            ['type' => 'TEXT','start_x'=>90,'start_y'=>923,'font_size'=>23,'row_height'=>33,'row_count'=>22,'text'=>$array['consignee_address']], // 收件人地址
            ['type' => 'TEXT','start_x'=>90,'start_y'=>1022,'font_size'=>21,'row_height'=>0,'row_count'=>0,'text'=>$array['consignor_info_co']], // 寄件人姓名
            ['type' => 'TEXT','start_x'=>90,'start_y'=>1054,'font_size'=>21,'row_height'=>33,'row_count'=>22,'text'=>$array['consignor_address']], // 寄件人地址

            ['type' => 'TEXT','start_x'=>10,'start_y'=>1145,'font_size'=>21,'row_height'=>0,'row_count'=>0,'text'=>'内件:'.$array['goods_name']], //物品
            ['type' => 'TEXT','start_x'=>10,'start_y'=>1170,'font_size'=>21,'row_height'=>33,'row_count'=>25,'text'=>$array['eps_remark']], // 留言

            ['type' => 'BARCODE','start_x'=>320,'start_y'=>1210,'width'=>440,'height'=>120,'data'=>$array['logistic_code']], // 条形码,

            ['type' => 'TEXT','start_x'=>90,'start_y'=>1371,'font_size'=>23,'row_height'=>0,'row_count'=>0,'text'=>$array['consignee_info_co']], // 收件人姓名
            ['type' => 'TEXT','start_x'=>90,'start_y'=>1405,'font_size'=>23,'row_height'=>33,'row_count'=>22,'text'=>$array['consignee_address']], // 收件人地址
            ['type' => 'TEXT','start_x'=>90,'start_y'=>1512,'font_size'=>21,'row_height'=>0,'row_count'=>0,'text'=>$array['consignor_info_co']], // 寄件人姓名
            ['type' => 'TEXT','start_x'=>90,'start_y'=>1544,'font_size'=>21,'row_height'=>33,'row_count'=>24,'text'=>$array['consignor_address']], // 寄件人地址

            ['type' => 'TEXT','start_x'=>10,'start_y'=>1630,'font_size'=>21,'row_height'=>0,'row_count'=>0,'text'=>'物品:'.$array['goods_name']], //物品
            ['type' => 'TEXT','start_x'=>10,'start_y'=>1660,'font_size'=>21,'row_height'=>33,'row_count'=>25,'text'=>$array['eps_remark']], // 留言
        ];
        if($array['eps_logo']) {
//            $template_data[] = ['type' => 'IMAGE', 'start_x' => 10, 'start_y' => 10, 'width' => 400, 'height' => 90]; // 快递图标,
            $template_data[] = ['type' => 'IMAGE', 'start_x' => 10, 'start_y' => 733, 'width' => 280, 'height' => 120]; // 快递图标,
        }

        if(isset($array['reprint'])){
            $template_data[] =['type' => 'TEXT','start_x'=>760,'start_y'=>255,'font_size'=>23,'row_height'=>0,'row_count'=>0,'text'=>'R']; // 重新打印标志
        }

        return ImageTemplateService::createFromPic($array['eps_shipper_code'],$array['logistic_code'],$back_file_path,$template_data);

    }
    /**
     * 没有图标菜鸟模板
     * @param array $data
     * @return string
     */
    public function getEMSCaiNiaoWithoutLogo($data=[]){
        $data['eps_logo']=0;
        return ImageTemplateService::getEMSCaiNiao($data);
    }
    //菜鸟模板
    public function getEMSCaiNiao($data=[]){

        $array=TemplateHelper::arrangeData($data);

        $back_file_path='cainiao.jpg';
        $template_data=[
            ['type' => 'TEXT','start_x'=>480,'start_y'=>80,'font_size'=>46,'row_height'=>0,'row_count'=>0,'text'=>$array['eps_exp_type_name']], // 快递业务类型,$data['eps_exp_type_name']
            //['type' => 'TEXT','start_x'=>10,'start_y'=>190,'font_size'=>55,'row_height'=>0,'row_count'=>0,'style'=>'bold','text'=>$array['mark_code']],//区
            ['type' => 'TEXT','start_x'=>102,'start_y'=>271,'font_size'=>45,'row_height'=>0,'row_count'=>0,'text'=>$array['package_name']],
            //['type' => 'BARCODE','start_x'=>570,'start_y'=>225,'width'=>200,'height'=>80,'data'=>$array['sorting_code']],  //分拣码条形码
            ['type' => 'TEXT','start_x'=>120,'start_y'=>331,'font_size'=>140,'row_height'=>0,'row_count'=>0,'color'=>'gray','style'=>'bold','text'=>$array['destinatio_code']],//目的编码
            ['type' => 'BARCODE','start_x'=>100,'start_y'=>530,'width'=>600,'height'=>148,'data'=>$array['logistic_code']], // 条形码,

            ['type' => 'TEXT','start_x'=>102,'start_y'=>335,'font_size'=>23,'row_height'=>0,'row_count'=>0,'text'=>$array['consignee_info']], // 收件人姓名
//            ['type' => 'TEXT','start_x'=>381,'start_y'=>345,'font_size'=>30,'row_height'=>0,'row_count'=>0,'text'=>$array['receiver_mobile']],
            ['type' => 'TEXT','start_x'=>102,'start_y'=>368,'font_size'=>23,'row_height'=>30,'row_count'=>22,'text'=>$array['consignee_address']], // 收件人地址
            //['type' => 'TEXT','start_x'=>102,'start_y'=>443,'font_size'=>18,'row_height'=>0,'row_count'=>0,'text'=>$array['consignor_info']], // 寄件人姓名
//            ['type' => 'TEXT','start_x'=>200,'start_y'=>448,'font_size'=>18,'row_height'=>0,'row_count'=>0,'text'=>$array['sender_mobile']],//寄件人电话
            //['type' => 'TEXT','start_x'=>102,'start_y'=>470,'font_size'=>16,'row_height'=>23,'row_count'=>30,'text'=>$array['consignor_address']], // 寄件人地址

            ['type' => 'TEXT','start_x'=>12,'start_y'=>709,'font_size'=>21,'row_height'=>0,'row_count'=>0,'text'=>'付款方式:'.$array['eps_pay_type']], // 付款方式
            ['type' => 'TEXT','start_x'=>12,'start_y'=>738,'font_size'=>21,'row_height'=>0,'row_count'=>0,'text'=>'计算重量(kg):'.$array['eps_weight']], // 计算重量
            ['type' => 'TEXT','start_x'=>12,'start_y'=>767,'font_size'=>21,'row_height'=>0,'row_count'=>0,'text'=>'保价金额(元):'.$array['insured_value']], // 保价金额
            ['type' => 'TEXT','start_x'=>12,'start_y'=>796,'font_size'=>21,'row_height'=>0,'row_count'=>0,'text'=>'数量:'.$array['eps_quantity']], // 数量
            ['type' => 'TEXT','start_x'=>12,'start_y'=>825,'font_size'=>21,'row_height'=>0,'row_count'=>0,'text'=>'签回单:'.$array['eps_need_return_cname']], // 签回单
            ['type' => 'TEXT','start_x'=>12,'start_y'=>855,'font_size'=>14,'row_height'=>0,'row_count'=>0,'text'=>$array['print_time']], // 打印时间

            ['type' => 'BARCODE','start_x'=>305,'start_y'=>906,'width'=>440,'height'=>75,'data'=>$array['logistic_code']], // 条形码

            ['type' => 'TEXT','start_x'=>102,'start_y'=>1008,'font_size'=>18,'row_height'=>0,'row_count'=>0,'text'=>$array['consignee_info_co']], // 收件人姓名
            ['type' => 'TEXT','start_x'=>102,'start_y'=>1035,'font_size'=>18,'row_height'=>30,'row_count'=>29,'text'=>$array['consignee_address']], // 收件人地址
            //['type' => 'TEXT','start_x'=>102,'start_y'=>1103,'font_size'=>16,'row_height'=>0,'row_count'=>0,'text'=>$array['consignor_info_co']], // 寄件人姓名
            //['type' => 'TEXT','start_x'=>102,'start_y'=>1124,'font_size'=>16,'row_height'=>28,'row_count'=>30,'text'=>$array['consignor_address']], // 寄件人地址
            ['type' => 'TEXT','start_x'=>20,'start_y'=>1194,'font_size'=>21,'row_height'=>0,'row_count'=>0,'text'=>'物品:'.$array['goods_name']], //物品
            ['type' => 'TEXT','start_x'=>20,'start_y'=>1230,'font_size'=>21,'row_height'=>30,'row_count'=>24,'text'=>$array['eps_remark']],  //留言

        ];
        //公司名
        if($array['receiver_company']){
            $co_startx=90+mb_strwidth($array['consignee_info'])*18;
            if(mb_strwidth($array['consignee_address'], 'UTF-8')/40>3){
                $template_data[] = ['type' => 'TEXT', 'start_x' => $co_startx, 'start_y' => 335+25, 'font_size' => 18, 'row_height' => 0, 'row_count' => 0, 'text' => $array['receiver_company']]; // 收件人公司
            }else {
                $template_data[] = ['type' => 'TEXT', 'start_x' => $co_startx, 'start_y' => 335, 'font_size' => 18, 'row_height' => 0, 'row_count' => 0, 'text' => $array['receiver_company']]; // 收件人公司
            }
        }
        if($array['eps_logo']) {
            $template_data[] = ['type' => 'IMAGE', 'start_x' => 10, 'start_y' => 10, 'width' => 400, 'height' => 90]; // 快递图标,
            $template_data[] = ['type' => 'IMAGE', 'start_x' => 10, 'start_y' => 897, 'width' => 280, 'height' => 80]; // 快递图标,
        }
        if($array['sender_company']){
            $co_startx=90+mb_strwidth($array['consignor_info'])*18;
            if(mb_strwidth($array['consignee_address'], 'UTF-8')/40>3){
                $template_data[] =['type' => 'TEXT','start_x'=>$co_startx,'start_y'=>443+25,'font_size'=>18,'row_height'=>0,'row_count'=>0,'text'=>$array['sender_company']]; // 寄件人公司
            }else{
                $template_data[] =['type' => 'TEXT','start_x'=>$co_startx,'start_y'=>443,'font_size'=>18,'row_height'=>0,'row_count'=>0,'text'=>$array['sender_company']]; // 寄件人公司
            }
        }

        if(isset($data['reprint'])){
            $template_data[] =['type' => 'TEXT','start_x'=>760,'start_y'=>190,'font_size'=>23,'row_height'=>0,'row_count'=>0,'text'=>'R']; // 重新打印标志
        }
        if(isset($data['sequence_number'])){
            $template_data[] =['type' => 'TEXT','start_x'=>20,'start_y'=>1420,'font_size'=>14,'row_height'=>0,'row_count'=>0,'text'=>$array['sequence_number']]; // 多件打印时的流水号
        }
        //寄件信息
        if(mb_strwidth($array['consignee_address'], 'UTF-8')/40>3){
            $template_data[] =['type' => 'TEXT','start_x'=>102,'start_y'=>443+25,'font_size'=>21,'row_height'=>0,'row_count'=>0,'text'=>$array['consignor_info']]; // 寄件人姓名
            $template_data[] =['type' => 'TEXT','start_x'=>102,'start_y'=>470+25,'font_size'=>21,'row_height'=>30,'row_count'=>25,'text'=>$array['consignor_address']];// 寄件人地址
            $template_data[] =['type' => 'TEXT','start_x'=>102,'start_y'=>1103+25,'font_size'=>16,'row_height'=>0,'row_count'=>0,'text'=>$array['consignor_info_co']]; // 寄件人姓名
            $template_data[] =['type' => 'TEXT','start_x'=>102,'start_y'=>1124+25,'font_size'=>16,'row_height'=>28,'row_count'=>30,'text'=>$array['consignor_address']]; // 寄件人地址
        }
        else{
            $template_data[] =['type' => 'TEXT','start_x'=>102,'start_y'=>443,'font_size'=>18,'row_height'=>0,'row_count'=>0,'text'=>$array['consignor_info']]; // 寄件人姓名
            $template_data[] =['type' => 'TEXT','start_x'=>102,'start_y'=>470,'font_size'=>18,'row_height'=>30,'row_count'=>29,'text'=>$array['consignor_address']];// 寄件人地址
            $template_data[] =['type' => 'TEXT','start_x'=>102,'start_y'=>1103,'font_size'=>16,'row_height'=>0,'row_count'=>0,'text'=>$array['consignor_info_co']]; // 寄件人姓名
            $template_data[] =['type' => 'TEXT','start_x'=>102,'start_y'=>1124,'font_size'=>16,'row_height'=>28,'row_count'=>30,'text'=>$array['consignor_address']]; // 寄件人地址
        }
        if(self::sstrlen($array['mark_code'])>14){
            $template_data[]=['type' => 'TEXT','start_x'=>10,'start_y'=>190,'font_size'=>50,'row_height'=>0,'row_count'=>0,'style'=>'bold','text'=>$array['mark_code']];//区
        }else{
            $template_data[]=['type' => 'TEXT','start_x'=>10,'start_y'=>190,'font_size'=>60,'row_height'=>0,'row_count'=>0,'style'=>'bold','text'=>$array['mark_code']];//区
        }
        if($array['package_code']){
            $template_data[]=['type' => 'BARCODE','start_x'=>488,'start_y'=>213,'width'=>250,'height'=>80,'data'=>$array['package_code']];//集包地编码圆通
        }
        return ImageTemplateService::createFromPic($array['eps_shipper_code'],$array['logistic_code'],$back_file_path,$template_data,false);
    }
    //ems菜鸟模板180===
    public function getEMSCaiNiao180($data=[]){

        $array=TemplateHelper::arrangeData($data);

        $back_file_path='cainiao.jpg';
        $template_data=[
            ['type' => 'TEXT','start_x'=>480,'start_y'=>80,'font_size'=>46,'row_height'=>0,'row_count'=>0,'text'=>$array['eps_exp_type_name']], // 快递业务类型,$data['eps_exp_type_name']
            //['type' => 'TEXT','start_x'=>10,'start_y'=>190,'font_size'=>60,'row_height'=>0,'row_count'=>0,'style'=>'bold','text'=>$array['mark_code']],//区
            ['type' => 'TEXT','start_x'=>102,'start_y'=>271,'font_size'=>45,'row_height'=>0,'row_count'=>0,'text'=>$array['package_name']],
            //['type' => 'BARCODE','start_x'=>570,'start_y'=>225,'width'=>200,'height'=>80,'data'=>$array['sorting_code']],  //分拣码条形码
            //['type' => 'TEXT','start_x'=>120,'start_y'=>331,'font_size'=>140,'row_height'=>0,'row_count'=>0,'color'=>'gray','style'=>'bold','text'=>$array['destinatio_code']],//目的编码
            ['type' => 'BARCODE','start_x'=>100,'start_y'=>530,'width'=>600,'height'=>148,'data'=>$array['logistic_code']], // 条形码,

            ['type' => 'TEXT','start_x'=>102,'start_y'=>335,'font_size'=>23,'row_height'=>0,'row_count'=>0,'text'=>$array['consignee_info']], // 收件人姓名
//            ['type' => 'TEXT','start_x'=>381,'start_y'=>345,'font_size'=>30,'row_height'=>0,'row_count'=>0,'text'=>$array['receiver_mobile']],
            ['type' => 'TEXT','start_x'=>102,'start_y'=>368,'font_size'=>23,'row_height'=>30,'row_count'=>22,'text'=>$array['consignee_address']], // 收件人地址
            //['type' => 'TEXT','start_x'=>102,'start_y'=>443,'font_size'=>18,'row_height'=>0,'row_count'=>0,'text'=>$array['consignor_info']], // 寄件人姓名
//            ['type' => 'TEXT','start_x'=>200,'start_y'=>448,'font_size'=>18,'row_height'=>0,'row_count'=>0,'text'=>$array['sender_mobile']],//寄件人电话
            //['type' => 'TEXT','start_x'=>102,'start_y'=>470,'font_size'=>16,'row_height'=>23,'row_count'=>30,'text'=>$array['consignor_address']], // 寄件人地址

            ['type' => 'TEXT','start_x'=>12,'start_y'=>709,'font_size'=>21,'row_height'=>0,'row_count'=>0,'text'=>'付款方式:'.$array['eps_pay_type']], // 付款方式
            ['type' => 'TEXT','start_x'=>12,'start_y'=>738,'font_size'=>21,'row_height'=>0,'row_count'=>0,'text'=>'计算重量(kg):'.$array['eps_weight']], // 计算重量
            ['type' => 'TEXT','start_x'=>12,'start_y'=>767,'font_size'=>21,'row_height'=>0,'row_count'=>0,'text'=>'保价金额(元):'.$array['insured_value']], // 保价金额
            ['type' => 'TEXT','start_x'=>12,'start_y'=>796,'font_size'=>21,'row_height'=>0,'row_count'=>0,'text'=>'数量:'.$array['eps_quantity']], // 数量
            ['type' => 'TEXT','start_x'=>12,'start_y'=>825,'font_size'=>21,'row_height'=>0,'row_count'=>0,'text'=>'签回单:'.$array['eps_need_return_cname']], // 签回单
            ['type' => 'TEXT','start_x'=>12,'start_y'=>855,'font_size'=>14,'row_height'=>0,'row_count'=>0,'text'=>$array['print_time']], // 打印时间

            ['type' => 'BARCODE','start_x'=>305,'start_y'=>906,'width'=>440,'height'=>75,'data'=>$array['logistic_code']], // 条形码

            ['type' => 'TEXT','start_x'=>102,'start_y'=>1008,'font_size'=>18,'row_height'=>0,'row_count'=>0,'text'=>$array['consignee_info_co']], // 收件人姓名
            ['type' => 'TEXT','start_x'=>102,'start_y'=>1035,'font_size'=>18,'row_height'=>30,'row_count'=>29,'text'=>$array['consignee_address']], // 收件人地址
            //['type' => 'TEXT','start_x'=>102,'start_y'=>1103,'font_size'=>16,'row_height'=>0,'row_count'=>0,'text'=>$array['consignor_info_co']], // 寄件人姓名
            //['type' => 'TEXT','start_x'=>102,'start_y'=>1124,'font_size'=>16,'row_height'=>28,'row_count'=>30,'text'=>$array['consignor_address']], // 寄件人地址
            ['type' => 'TEXT','start_x'=>20,'start_y'=>1194,'font_size'=>21,'row_height'=>0,'row_count'=>0,'text'=>'物品:'.$array['goods_name']], //物品
            ['type' => 'TEXT','start_x'=>20,'start_y'=>1230,'font_size'=>21,'row_height'=>30,'row_count'=>24,'text'=>$array['eps_remark']],  //留言

        ];
        //公司名
        if($array['receiver_company']){
            $co_startx=90+mb_strwidth($array['consignee_info'])*18;
            if(mb_strwidth($array['consignee_address'], 'UTF-8')/40>3){
                $template_data[] = ['type' => 'TEXT', 'start_x' => $co_startx, 'start_y' => 335+25, 'font_size' => 18, 'row_height' => 0, 'row_count' => 0, 'text' => $array['receiver_company']]; // 收件人公司
            }else {
                $template_data[] = ['type' => 'TEXT', 'start_x' => $co_startx, 'start_y' => 335, 'font_size' => 18, 'row_height' => 0, 'row_count' => 0, 'text' => $array['receiver_company']]; // 收件人公司
            }
        }
        if($array['sender_company']){
            $co_startx=90+mb_strwidth($array['consignor_info'])*18;
            if(mb_strwidth($array['consignee_address'], 'UTF-8')/40>3){
                $template_data[] =['type' => 'TEXT','start_x'=>$co_startx,'start_y'=>443+25,'font_size'=>18,'row_height'=>0,'row_count'=>0,'text'=>$array['sender_company']]; // 寄件人公司
            }else{
                $template_data[] =['type' => 'TEXT','start_x'=>$co_startx,'start_y'=>443,'font_size'=>18,'row_height'=>0,'row_count'=>0,'text'=>$array['sender_company']]; // 寄件人公司
            }
        }

        if(isset($data['reprint'])){
            $template_data[] =['type' => 'TEXT','start_x'=>760,'start_y'=>190,'font_size'=>23,'row_height'=>0,'row_count'=>0,'text'=>'R']; // 重新打印标志
        }
        if(isset($data['sequence_number'])){
            $template_data[] =['type' => 'TEXT','start_x'=>20,'start_y'=>1420,'font_size'=>14,'row_height'=>0,'row_count'=>0,'text'=>$array['sequence_number']]; // 多件打印时的流水号
        }
        //寄件信息
        if(mb_strwidth($array['consignee_address'], 'UTF-8')/40>3){
            $template_data[] =['type' => 'TEXT','start_x'=>102,'start_y'=>443+25,'font_size'=>21,'row_height'=>0,'row_count'=>0,'text'=>$array['consignor_info']]; // 寄件人姓名
            $template_data[] =['type' => 'TEXT','start_x'=>102,'start_y'=>470+25,'font_size'=>21,'row_height'=>30,'row_count'=>25,'text'=>$array['consignor_address']];// 寄件人地址
            $template_data[] =['type' => 'TEXT','start_x'=>102,'start_y'=>1103+25,'font_size'=>16,'row_height'=>0,'row_count'=>0,'text'=>$array['consignor_info_co']]; // 寄件人姓名
            $template_data[] =['type' => 'TEXT','start_x'=>102,'start_y'=>1124+25,'font_size'=>16,'row_height'=>28,'row_count'=>30,'text'=>$array['consignor_address']]; // 寄件人地址
        }
        else{
            $template_data[] =['type' => 'TEXT','start_x'=>102,'start_y'=>443,'font_size'=>18,'row_height'=>0,'row_count'=>0,'text'=>$array['consignor_info']]; // 寄件人姓名
            $template_data[] =['type' => 'TEXT','start_x'=>102,'start_y'=>470,'font_size'=>18,'row_height'=>30,'row_count'=>29,'text'=>$array['consignor_address']];// 寄件人地址
            $template_data[] =['type' => 'TEXT','start_x'=>102,'start_y'=>1103,'font_size'=>16,'row_height'=>0,'row_count'=>0,'text'=>$array['consignor_info_co']]; // 寄件人姓名
            $template_data[] =['type' => 'TEXT','start_x'=>102,'start_y'=>1124,'font_size'=>16,'row_height'=>28,'row_count'=>30,'text'=>$array['consignor_address']]; // 寄件人地址
        }
        if(self::sstrlen($array['mark_code'])>14){
            $template_data[]=['type' => 'TEXT','start_x'=>10,'start_y'=>190,'font_size'=>50,'row_height'=>0,'row_count'=>0,'style'=>'bold','text'=>$array['mark_code']];//区
        }else{
            $template_data[]=['type' => 'TEXT','start_x'=>10,'start_y'=>190,'font_size'=>60,'row_height'=>0,'row_count'=>0,'style'=>'bold','text'=>$array['mark_code']];//区
        }
        if($array['package_code']){
            $template_data[]=['type' => 'BARCODE','start_x'=>488,'start_y'=>213,'width'=>250,'height'=>80,'data'=>$array['package_code']];//集包地编码圆通
        }
        return ImageTemplateService::createFromPic($array['eps_shipper_code'],$array['logistic_code'],$back_file_path,$template_data,false);
    }
    //天天模板
    public function getCNTianTian($data=[]){

        $array=TemplateHelper::arrangeData($data);

        $back_file_path='tiantian.jpg';
        $template_data=[
            ['type' => 'TEXT','start_x'=>582,'start_y'=>70,'font_size'=>23,'row_height'=>0,'row_count'=>0,'text'=>$array['eps_exp_type_name']], // 快递业务类型,$data['eps_exp_type_name']
            ['type' => 'TEXT','start_x'=>34,'start_y'=>295,'font_size'=>45,'row_height'=>0,'row_count'=>0,'text'=>$array['package_name']],
            ['type' => 'BARCODE','start_x'=>100,'start_y'=>578,'width'=>600,'height'=>130,'data'=>$array['logistic_code']], // 条形码,
            ['type' => 'QrCode','start_x'=>629,'start_y'=>719,'width'=>130,'height'=>50,'text'=>$array['logistic_code']], // 二维码
            ['type' => 'QrCode','start_x'=>629,'start_y'=>977,'width'=>130,'height'=>50,'text'=>$array['logistic_code']], // 二维码

           ['type' => 'TEXT','start_x'=>60,'start_y'=>352,'font_size'=>22,'row_height'=>0,'row_count'=>0,'text'=>$array['consignee_info']], // 收件人姓名
//            ['type' => 'TEXT','start_x'=>381,'start_y'=>345,'font_size'=>30,'row_height'=>0,'row_count'=>0,'text'=>$array['receiver_mobile']],
            ['type' => 'TEXT','start_x'=>60,'start_y'=>386,'font_size'=>22,'row_height'=>33,'row_count'=>16,'text'=>$array['consignee_address']], // 收件人地址
            ['type' => 'TEXT','start_x'=>60,'start_y'=>497,'font_size'=>20,'row_height'=>0,'row_count'=>0,'text'=>$array['consignor_info']], // 寄件人姓名
//            ['type' => 'TEXT','start_x'=>200,'start_y'=>448,'font_size'=>18,'row_height'=>0,'row_count'=>0,'text'=>$array['sender_mobile']],//寄件人电话
            ['type' => 'TEXT','start_x'=>60,'start_y'=>527,'font_size'=>20,'row_height'=>30,'row_count'=>17,'text'=>$array['consignor_address']], // 寄件人地址

            ['type' => 'TEXT','start_x'=>550,'start_y'=>440,'font_size'=>21,'row_height'=>0,'row_count'=>0,'text'=>'付款方式:'.$array['eps_pay_type']], // 付款方式
            //['type' => 'TEXT','start_x'=>12,'start_y'=>738,'font_size'=>21,'row_height'=>0,'row_count'=>0,'text'=>'计算重量(kg):'.$array['eps_weight']], // 计算重量
            ['type' => 'TEXT','start_x'=>550,'start_y'=>480,'font_size'=>21,'row_height'=>0,'row_count'=>0,'text'=>'保价金额(元):'.$array['insured_value']], // 保价金额
            ['type' => 'TEXT','start_x'=>550,'start_y'=>520,'font_size'=>21,'row_height'=>0,'row_count'=>0,'text'=>'数量:'.$array['eps_quantity']], // 数量
            //['type' => 'TEXT','start_x'=>12,'start_y'=>825,'font_size'=>21,'row_height'=>0,'row_count'=>0,'text'=>'签回单:'.$array['eps_need_return_cname']], // 签回单
            ['type' => 'TEXT','start_x'=>550,'start_y'=>395,'font_size'=>21,'row_height'=>0,'row_count'=>0,'text'=>date('Y/m/d', time())], // 打印时间
            ['type' => 'TEXT','start_x'=>386,'start_y'=>857,'font_size'=>14,'row_height'=>0,'row_count'=>0,'text'=>date('Y-m-d H:i:s', time())], // 打印时间

            ['type' => 'BARCODE','start_x'=>345,'start_y'=>873,'width'=>440,'height'=>90,'data'=>$array['logistic_code']], // 条形码

            ['type' => 'TEXT','start_x'=>60,'start_y'=>1000,'font_size'=>18,'row_height'=>0,'row_count'=>0,'text'=>$array['consignee_info_co']], // 收件人姓名
            ['type' => 'TEXT','start_x'=>60,'start_y'=>1025,'font_size'=>18,'row_height'=>30,'row_count'=>22,'text'=>$array['consignee_address']], // 收件人地址
            ['type' => 'TEXT','start_x'=>60,'start_y'=>1088,'font_size'=>16,'row_height'=>0,'row_count'=>0,'text'=>$array['consignor_info_co']], // 寄件人姓名
            ['type' => 'TEXT','start_x'=>60,'start_y'=>1111,'font_size'=>16,'row_height'=>25,'row_count'=>25,'text'=>$array['consignor_address']], // 寄件人地址
            ['type' => 'TEXT','start_x'=>20,'start_y'=>1179,'font_size'=>21,'row_height'=>0,'row_count'=>0,'text'=>'物品:'.$array['goods_name']], //物品
            ['type' => 'TEXT','start_x'=>20,'start_y'=>1215,'font_size'=>21,'row_height'=>30,'row_count'=>24,'text'=>$array['eps_remark']],
            ['type' => 'TEXT','start_x'=>551,'start_y'=>1276,'font_size'=>55,'row_height'=>0,'row_count'=>0,'text'=>$array['phone_tail_number']],//手机尾号

        ];
        //大头笔大小调整
        if(self::sstrlen($array['mark_code'])>14){
            $template_data[]=['type' => 'TEXT','start_x'=>5,'start_y'=>200,'font_size'=>50,'row_height'=>0,'row_count'=>0,'text'=>$array['mark_code']];
        }else{
            $template_data[]=['type' => 'TEXT','start_x'=>5,'start_y'=>200,'font_size'=>55,'row_height'=>0,'row_count'=>0,'text'=>$array['mark_code']];
        }
        //公司名
        if($array['receiver_company']){
            $co_startx=90+mb_strwidth($array['consignee_info'])*18;
            $template_data[] =['type' => 'TEXT','start_x'=>$co_startx,'start_y'=>335,'font_size'=>18,'row_height'=>0,'row_count'=>0,'text'=>$array['receiver_company']]; // 收件人公司
        }

        if(isset($data['reprint'])){
            $template_data[] =['type' => 'TEXT','start_x'=>760,'start_y'=>190,'font_size'=>23,'row_height'=>0,'row_count'=>0,'text'=>'R']; // 重新打印标志
        }
        if(isset($data['sequence_number'])){
            $template_data[] =['type' => 'TEXT','start_x'=>20,'start_y'=>1420,'font_size'=>14,'row_height'=>0,'row_count'=>0,'text'=>$array['sequence_number']]; // 多件打印时的流水号
        }
        return ImageTemplateService::createFromPic($array['eps_shipper_code'],$array['logistic_code'],$back_file_path,$template_data,false);
    }

    public static function getEMS2($data){
        $array=TemplateHelper::arrangeData($data);

        $width=800;
        $height=1440;

        $back_file_path='g2.jpg';
        $template_data=[
            ['type' => 'TEXT','start_x'=>480,'start_y'=>80,'font_size'=>46,'row_height'=>0,'row_count'=>0,'text'=>$array['eps_exp_type_name']], // 快递业务类型,$data['eps_exp_type_name']

            ['type' => 'BARCODE','start_x'=>160,'start_y'=>225,'width'=>480,'height'=>100,'data'=>$array['logistic_code']], // 条形码,

            ['type' => 'TEXT','start_x'=>90,'start_y'=>362,'font_size'=>23,'row_height'=>0,'row_count'=>0,'text'=>$array['consignee_info']], // 收件人姓名
            ['type' => 'TEXT','start_x'=>90,'start_y'=>526,'font_size'=>23,'row_height'=>0,'row_count'=>0,'text'=>$array['consignor_info']], // 寄件人姓名

            ['type' => 'TEXT','start_x'=>10,'start_y'=>691,'font_size'=>23,'row_height'=>0,'row_count'=>0,'text'=>'付款方式:'.$array['eps_pay_type']], // 付款方式
            ['type' => 'TEXT','start_x'=>10,'start_y'=>727,'font_size'=>23,'row_height'=>0,'row_count'=>0,'text'=>'计算重量(kg):'.$array['eps_weight']], // 计算重量
            ['type' => 'TEXT','start_x'=>10,'start_y'=>763,'font_size'=>23,'row_height'=>0,'row_count'=>0,'text'=>'保价金额(元):'.$array['insured_value']], // 保价金额
            ['type' => 'TEXT','start_x'=>10,'start_y'=>799,'font_size'=>23,'row_height'=>0,'row_count'=>0,'text'=>'数量:'.$array['eps_quantity']], // 数量
            ['type' => 'TEXT','start_x'=>10,'start_y'=>835,'font_size'=>23,'row_height'=>0,'row_count'=>0,'text'=>'签回单:'.$array['eps_need_return_cname']], // 签回单
            ['type' => 'TEXT','start_x'=>10,'start_y'=>862,'font_size'=>14,'row_height'=>0,'row_count'=>0,'text'=>$array['print_time']], // 打印时间

            ['type' => 'TEXT','start_x'=>450,'start_y'=>691,'font_size'=>23,'row_height'=>0,'row_count'=>0,'text'=>'签收人:'], // 签收人
            ['type' => 'TEXT','start_x'=>450,'start_y'=>737,'font_size'=>23,'row_height'=>0,'row_count'=>0,'text'=>'签收时间:'], // 签收时间

            ['type' => 'BARCODE','start_x'=>10,'start_y'=>887,'width'=>440,'height'=>120,'data'=>$array['logistic_code']], // 条形码,

            ['type' => 'TEXT','start_x'=>90,'start_y'=>1051,'font_size'=>23,'row_height'=>0,'row_count'=>0,'text'=>$array['consignee_info_co']], // 收件人姓名
            ['type' => 'TEXT','start_x'=>90,'start_y'=>1089,'font_size'=>23,'row_height'=>33,'row_count'=>22,'text'=>$array['consignee_address']], // 收件人地址
            ['type' => 'TEXT','start_x'=>90,'start_y'=>1191,'font_size'=>23,'row_height'=>0,'row_count'=>0,'text'=>$array['consignor_info_co']], // 寄件人姓名
            ['type' => 'TEXT','start_x'=>90,'start_y'=>1229,'font_size'=>23,'row_height'=>33,'row_count'=>22,'text'=>$array['consignor_address']], // 寄件人地址

            ['type' => 'TEXT','start_x'=>10,'start_y'=>1332,'font_size'=>23,'row_height'=>0,'row_count'=>0,'text'=>'物品:'.$array['goods_name']], //物品
            ['type' => 'TEXT','start_x'=>10,'start_y'=>1378,'font_size'=>23,'row_height'=>33,'row_count'=>25,'text'=>$array['eps_remark']], // 留言
        ];
        $num_array=countStringNumber($array['mark_code']);
        $mark_start_x=($width-$num_array['ch']*58-$num_array['al']*48)/2;
        $template_data[] =['type' => 'TEXT','start_x'=>$mark_start_x,'start_y'=>187,'font_size'=>58,'row_height'=>0,'row_count'=>0,'text'=>$array['mark_code']]; // 大头笔
        //公司名
        if($data['receiver_company']){
            $co_startx=90+mb_strwidth($array['consignee_info'])*18;
            $template_data[] =['type' => 'TEXT','start_x'=>$co_startx,'start_y'=>362,'font_size'=>18,'row_height'=>0,'row_count'=>0,'text'=>$array['receiver_company']]; // 收件人公司
        }
        $template_data[] =['type' => 'TEXT','start_x'=>90,'start_y'=>400,'font_size'=>23,'row_height'=>33,'row_count'=>22,'text'=>$array['consignee_address']]; // 收件人地址

        if($data['sender_company']){
            $co_startx=90+mb_strwidth($array['consignor_info'])*18;
            $template_data[] =['type' => 'TEXT','start_x'=>$co_startx,'start_y'=>526,'font_size'=>18,'row_height'=>0,'row_count'=>0,'text'=>$array['sender_company']]; // 寄件人公司
        }
        $template_data[] =['type' => 'TEXT','start_x'=>90,'start_y'=>564,'font_size'=>23,'row_height'=>33,'row_count'=>22,'text'=>$array['consignor_address']]; // 寄件人地址

        $template_data[] = ['type' => 'IMAGE', 'start_x' => 10, 'start_y' => 10, 'width' => 400, 'height' => 90]; // 快递图标,

        if(isset($array['reprint'])){
            $template_data[] =['type' => 'TEXT','start_x'=>760,'start_y'=>255,'font_size'=>23,'row_height'=>0,'row_count'=>0,'text'=>'R']; // 重新打印标志
        }
        if(isset($array['sequence_number'])){
            $template_data[] =['type' => 'TEXT','start_x'=>15,'start_y'=>295,'font_size'=>14,'row_height'=>0,'row_count'=>0,'text'=>$array['sequence_number']]; // 多件打印时的流水号
        }

        return ImageTemplateService::createFromPic($array['eps_shipper_code'],$array['logistic_code'],$back_file_path,$template_data);
    }
    /**
     * 没有图标通用两联
     * @param array $data
     * @return string
     */
    public function getGeneral2WithoutLogo($data=[]){
        $data['eps_logo']=0;
        return ImageTemplateService::getGeneral2($data);
    }

    /**
     * 没有图标通用三联
     * @param array $data
     * @return string
     */
    public function getGeneral3WithoutLogo($data=[]){
        $data['eps_logo']=0;
        return ImageTemplateService::getGeneral3($data);
    }

    public function getHHTT($data=[]){

        $data['eps_logo']=0;
        return ImageTemplateService::getGeneral2($data);
    }
    public function getSF150($data=[]){

        $array=TemplateHelper::arrangeData($data);

        $back_file_path='sf150.jpg';
        $template_data=[
//          ['type' => 'IMAGE','start_x'=>10,'start_y'=>10,'width'=>400,'height'=>90], // 快递图标,
            ['type' => 'TEXT','start_x'=>480,'start_y'=>60,'font_size'=>46,'row_height'=>0,'row_count'=>0,'text'=>$array['eps_exp_type_name']], // 快递业务类型,$data['eps_exp_type_name']
            ['type' => 'TEXT','start_x'=>160,'start_y'=>160,'font_size'=>50,'row_height'=>0,'row_count'=>0,'text'=>$array['mark_code']], // 大头笔
            ['type' => 'BARCODE','start_x'=>160,'start_y'=>180,'width'=>450,'height'=>90,'data'=>$array['logistic_code']], // 条形码,

            ['type' => 'TEXT','start_x'=>90,'start_y'=>302,'font_size'=>23,'row_height'=>0,'row_count'=>0,'text'=>$array['consignee_info_co']], // 收件人姓名
            ['type' => 'TEXT','start_x'=>90,'start_y'=>336,'font_size'=>23,'row_height'=>33,'row_count'=>23,'text'=>$array['consignee_address']], // 收件人地址
            ['type' => 'TEXT','start_x'=>90,'start_y'=>438,'font_size'=>23,'row_height'=>0,'row_count'=>0,'text'=>$array['consignor_info_co']], // 寄件人姓名
            ['type' => 'TEXT','start_x'=>90,'start_y'=>478,'font_size'=>23,'row_height'=>33,'row_count'=>23,'text'=>$array['consignor_address']], // 寄件人地址

            ['type' => 'TEXT','start_x'=>10,'start_y'=>580,'font_size'=>23,'row_height'=>0,'row_count'=>0,'text'=>'付款方式:'.$array['eps_pay_type']], // 付款方式
            ['type' => 'TEXT','start_x'=>10,'start_y'=>616,'font_size'=>23,'row_height'=>0,'row_count'=>0,'text'=>'计算重量(kg):'.$array['eps_weight']], // 计算重量
            ['type' => 'TEXT','start_x'=>10,'start_y'=>652,'font_size'=>23,'row_height'=>0,'row_count'=>0,'text'=>'保价金额(元):'.$array['insured_value']], // 保价金额
            ['type' => 'TEXT','start_x'=>10,'start_y'=>688,'font_size'=>23,'row_height'=>0,'row_count'=>0,'text'=>'数量:'.$array['eps_quantity']], // 数量
            ['type' => 'TEXT','start_x'=>10,'start_y'=>724,'font_size'=>23,'row_height'=>0,'row_count'=>0,'text'=>'签回单:'.$array['eps_need_return_cname']], // 签回单

            ['type' => 'TEXT','start_x'=>450,'start_y'=>725,'font_size'=>14,'row_height'=>0,'row_count'=>0,'text'=>$array['print_time']], // 打印时间
            ['type' => 'TEXT','start_x'=>450,'start_y'=>580,'font_size'=>23,'row_height'=>0,'row_count'=>0,'text'=>'签收人:'], // 签收人
            ['type' => 'TEXT','start_x'=>450,'start_y'=>620,'font_size'=>23,'row_height'=>0,'row_count'=>0,'text'=>'签收时间:'], // 签收时间

            //['type' => 'IMAGE','start_x'=>10,'start_y'=>887,'width'=>280,'height'=>120], // 快递图标,
            ['type' => 'BARCODE','start_x'=>320,'start_y'=>738,'width'=>440,'height'=>100,'data'=>$array['logistic_code']], // 条形码,

            ['type' => 'TEXT','start_x'=>90,'start_y'=>880,'font_size'=>23,'row_height'=>0,'row_count'=>0,'text'=>$array['consignee_info_co']], // 收件人姓名
            ['type' => 'TEXT','start_x'=>90,'start_y'=>916,'font_size'=>23,'row_height'=>33,'row_count'=>23,'text'=>$array['consignee_address']], // 收件人地址
            ['type' => 'TEXT','start_x'=>90,'start_y'=>996,'font_size'=>23,'row_height'=>0,'row_count'=>0,'text'=>$array['consignor_info_co']], // 寄件人姓名
            ['type' => 'TEXT','start_x'=>90,'start_y'=>1032,'font_size'=>23,'row_height'=>33,'row_count'=>23,'text'=>$array['consignor_address']], // 寄件人地址

            ['type' => 'TEXT','start_x'=>10,'start_y'=>1115,'font_size'=>23,'row_height'=>0,'row_count'=>0,'text'=>'物品:'.$array['goods_name']], //物品
            ['type' => 'TEXT','start_x'=>10,'start_y'=>1150,'font_size'=>23,'row_height'=>33,'row_count'=>25,'text'=>$array['eps_remark']], // 留言
        ];
        return ImageTemplateService::createFromPic($array['eps_shipper_code'],$array['logistic_code'],$back_file_path,$template_data,true);
    }
    public function getEMS150($data=[]){

        $array=TemplateHelper::arrangeData($data);

        $back_file_path='ems150.jpg';
        $template_data=[
//            ['type' => 'IMAGE','start_x'=>10,'start_y'=>10,'width'=>400,'height'=>90], // 快递图标,
            ['type' => 'TEXT','start_x'=>50,'start_y'=>135,'font_size'=>39,'row_height'=>0,'row_count'=>0,'text'=>$array['eps_exp_type_name']], // 快递业务类型,$data['eps_exp_type_name']
            ['type' => 'TEXT','start_x'=>374,'start_y'=>233,'font_size'=>42,'row_height'=>0,'row_count'=>0,'text'=>$array['mark_code']], // 大头笔
            ['type' => 'BARCODE','start_x'=>320,'start_y'=>18,'width'=>450,'height'=>148,'data'=>$array['logistic_code']], // 条形码,


            ['type' => 'TEXT','start_x'=>22,'start_y'=>335,'font_size'=>18,'row_height'=>0,'row_count'=>0,'text'=>'收件: '.$array['consignee_info_co']], // 收件人姓名
            ['type' => 'TEXT','start_x'=>82,'start_y'=>375,'font_size'=>23,'row_height'=>33,'row_count'=>16,'text'=>$array['consignee_address']], // 收件人地址
            ['type' => 'TEXT','start_x'=>22,'start_y'=>216,'font_size'=>18,'row_height'=>0,'row_count'=>0,'text'=>'寄件: '.$array['sender_name']], // 寄件人姓名
            ['type' => 'TEXT','start_x'=>82,'start_y'=>244,'font_size'=>18,'row_height'=>0,'row_count'=>0,'text'=>$array['sender_mobile']],//寄件人电话
            ['type' => 'TEXT','start_x'=>182,'start_y'=>216,'font_size'=>14,'row_height'=>0,'row_count'=>0,'text'=>'('.$array['sender_company'].')'],//寄件人公司
            ['type' => 'TEXT','start_x'=>82,'start_y'=>265,'font_size'=>14,'row_height'=>20,'row_count'=>17,'text'=>$array['consignor_address']], // 寄件人地址

            ['type' => 'TEXT','start_x'=>22,'start_y'=>492,'font_size'=>15,'row_height'=>0,'row_count'=>0,'text'=>'付款方式 :'.$array['eps_pay_type']], // 付款方式
            ['type' => 'TEXT','start_x'=>22,'start_y'=>517,'font_size'=>15,'row_height'=>0,'row_count'=>0,'text'=>'计算重量(kg) :'.$array['eps_weight']], // 计算重量
            ['type' => 'TEXT','start_x'=>22,'start_y'=>542,'font_size'=>15,'row_height'=>0,'row_count'=>0,'text'=>'保价金额(元) :'.$array['insured_value']], // 保价金额
            ['type' => 'TEXT','start_x'=>612,'start_y'=>578,'font_size'=>15,'row_height'=>0,'row_count'=>0,'text'=>'数量:'.$array['eps_quantity']], // 数量
            ['type' => 'TEXT','start_x'=>694,'start_y'=>578,'font_size'=>15,'row_height'=>0,'row_count'=>0,'text'=>'签回单:'.$array['eps_need_return_cname']], // 签回单

            ['type' => 'TEXT','start_x'=>25,'start_y'=>169,'font_size'=>12,'row_height'=>0,'row_count'=>0,'text'=>$array['print_time']], // 打印时间
            ['type' => 'TEXT','start_x'=>408,'start_y'=>460,'font_size'=>15,'row_height'=>0,'row_count'=>0,'text'=>'签收人:'], // 签收人
            ['type' => 'TEXT','start_x'=>408,'start_y'=>495,'font_size'=>15,'row_height'=>0,'row_count'=>0,'text'=>'签收时间:'], // 签收时间

//            ['type' => 'IMAGE','start_x'=>10,'start_y'=>887,'width'=>280,'height'=>120], // 快递图标,
            ['type' => 'BARCODE','start_x'=>30,'start_y'=>750,'width'=>440,'height'=>100,'data'=>$array['logistic_code']], // 条形码,

            ['type' => 'TEXT','start_x'=>346,'start_y'=>902,'font_size'=>18,'row_height'=>0,'row_count'=>0,'text'=>'收件: '.$array['consignee_info_co']], // 收件人姓名
            ['type' => 'TEXT','start_x'=>406,'start_y'=>938,'font_size'=>18,'row_height'=>33,'row_count'=>16,'text'=>$array['consignee_address']], // 收件人地址
            ['type' => 'TEXT','start_x'=>22,'start_y'=>902,'font_size'=>18,'row_height'=>0,'row_count'=>0,'text'=>'寄件: '.$array['sender_name']], // 寄件人姓名
            ['type' => 'TEXT','start_x'=>81,'start_y'=>930,'font_size'=>18,'row_height'=>0,'row_count'=>0,'text'=>$array['sender_mobile']],//寄件人电话
            ['type' => 'TEXT','start_x'=>182,'start_y'=>902,'font_size'=>18,'row_height'=>0,'row_count'=>0,'text'=>'('.$array['sender_company'].')'],//寄件人公司
            ['type' => 'TEXT','start_x'=>81,'start_y'=>951,'font_size'=>12,'row_height'=>20,'row_count'=>16,'text'=>$array['consignor_address']], // 寄件人地址
            ['type' => 'TEXT','start_x'=>22,'start_y'=>631,'font_size'=>15,'row_height'=>0,'row_count'=>0,'text'=>'物品:'.$array['goods_name']], //物品
            ['type' => 'TEXT','start_x'=>22,'start_y'=>1033,'font_size'=>15,'row_height'=>33,'row_count'=>25,'text'=>'备注: '.$array['eps_remark']], // 留言
        ];
        if($data['eps_pay_type']==2){
            $template_data[]=['type' => 'TEXT','start_x'=>620,'start_y'=>343,'font_size'=>23,'row_height'=>0,'row_count'=>0,'text'=>'收件人付费']; // 收件人付费
            $template_data[]=['type' => 'TEXT','start_x'=>615,'start_y'=>407,'font_size'=>23,'row_height'=>33,'row_count'=>23,'text'=>'￥'.$array['COD'].'元']; // 付费金额
        }
        if (isset($array['receiver_identity'])) {
            $pushData = ['type' => 'TEXT','start_x'=>22,'start_y'=>666,'font_size'=>15,'row_height'=>0,'row_count'=>0,'text'=>'身份证：'.$array['receiver_identity_name'].' '.substr_replace($array['receiver_identity'], '***********', 3,11)];//身份证信息
            $template_data[] = $pushData;
        }
        return ImageTemplateService::createFromPic($array['eps_shipper_code'],$array['logistic_code'],$back_file_path,$template_data,false);
    }
    /**
     * 韵达的201模板
     * @param array $data
     * @return string
     */
    public function getYD201($data=[]){
        $array=TemplateHelper::arrangeData($data);
        //二维码 运单号+package_code+‘ ’+mark_destination
        $back_file_path='yd201.jpg';
        $template_data=[
            ['type' => 'TEXT','start_x'=>14,'start_y'=>27,'font_size'=>16,'row_height'=>0,'row_count'=>0,'text'=>'始发网点：'.$data['origin_name']], // 始发网点
            ['type' => 'TEXT','start_x'=>14,'start_y'=>55,'font_size'=>16,'row_height'=>0,'row_count'=>0,'text'=>'寄件人：'.$array['sender_name']], // 寄件人姓名
            ['type' => 'TEXT','start_x'=>14,'start_y'=>80,'font_size'=>16,'row_height'=>0,'row_count'=>0,'text'=>'寄件人电话：'.$array['sender_mobile']], // 寄件人电话
            ['type' => 'TEXT','start_x'=>14,'start_y'=>105,'font_size'=>16,'row_height'=>0,'row_count'=>0,'text'=>'寄件人公司：'.$array['sender_company']], // 寄件人公司
            ['type' => 'TEXT','start_x'=>14,'start_y'=>130,'font_size'=>16,'row_height'=>33,'row_count'=>38,'text'=>'寄件人地址：'.$array['consignor_address']], // 寄件人地址

            ['type' => 'TEXT','start_x'=>103,'start_y'=>170,'font_size'=>23,'row_height'=>0,'row_count'=>0,'text'=>'收件人：'.$data['receiver_name']], // 收件人姓名
            ['type' => 'TEXT','start_x'=>103,'start_y'=>205,'font_size'=>23,'row_height'=>0,'row_count'=>0,'text'=>'收件人电话：'.$data['receiver_mobile']], // 收件人电话
            ['type' => 'TEXT','start_x'=>103,'start_y'=>240,'font_size'=>23,'row_height'=>0,'row_count'=>0,'text'=>'收件人公司：'.$data['receiver_company']], // 收件人公司
            ['type' => 'TEXT','start_x'=>103,'start_y'=>275,'font_size'=>23,'row_height'=>35,'row_count'=>22,'text'=>'收件人地址：'.$array['consignee_address']], // 收件人地址

            ['type' => 'TEXT','start_x'=>14,'start_y'=>375,'font_size'=>40,'row_height'=>50,'row_count'=>0,'text'=>$array['package_name']], // 集包地
            ['type' => 'TEXT','start_x'=>15,'start_y'=>375,'font_size'=>40,'row_height'=>50,'row_count'=>0,'text'=>$array['package_name']], // 集包地

            ['type' => 'QrCode','start_x'=>24,'start_y'=>415,'width'=>180,'text'=>$array['sorting_code']], // 二维码

            //如果需要加粗,让x坐标加1
            ['type' => 'TEXT','start_x'=>454,'start_y'=>475,'font_size'=>60,'row_height'=>70,'row_count'=>0,'text'=>substr($array['mark_destination'],0,3)], // position
            ['type' => 'TEXT','start_x'=>455,'start_y'=>475,'font_size'=>60,'row_height'=>70,'row_count'=>0,'text'=>substr($array['mark_destination'],0,3)], // position

            ['type' => 'TEXT','start_x'=>275,'start_y'=>573,'font_size'=>60,'row_height'=>70,'row_count'=>0,'text'=>substr($array['mark_destination'],3)], // position_no
            ['type' => 'TEXT','start_x'=>276,'start_y'=>573,'font_size'=>60,'row_height'=>70,'row_count'=>0,'text'=>substr($array['mark_destination'],3)], // position_no

            ['type' => 'BARCODE','start_x'=>180,'start_y'=>620,'width'=>480,'height'=>100,'font'=>0,'data'=>$array['logistic_code']], // 条形码,
            ['type' => 'TEXT','start_x'=>14,'start_y'=>770,'font_size'=>23,'row_height'=>33,'row_count'=>23,'text'=>'运单编号：'.$array['logistic_code']], // 运单编号

            ['type' => 'TEXT','start_x'=>14,'start_y'=>865,'font_size'=>14,'row_height'=>0,'row_count'=>0,'text'=>$array['print_time']], // 打印时间

            ['type' => 'TEXT','start_x'=>14,'start_y'=>920,'font_size'=>23,'row_height'=>0,'row_count'=>0,'text'=>'订单号：'.$array['eps_thr_order_code']], // 订单号
            ['type' => 'TEXT','start_x'=>14,'start_y'=>955,'font_size'=>23,'row_height'=>0,'row_count'=>0,'text'=>'物品：'.$array['goods_name']], //物品
            ['type' => 'TEXT','start_x'=>14,'start_y'=>990,'font_size'=>23,'row_height'=>33,'row_count'=>25,'text'=>'留言：'.$array['eps_remark']], // 留言

            ['type' => 'TEXT','start_x'=>14,'start_y'=>1260,'font_size'=>23,'row_height'=>33,'row_count'=>23,'text'=>$array['logistic_code']], // 运单编号
            ['type' => 'BARCODE','start_x'=>270,'start_y'=>1223,'width'=>300,'height'=>80,'font'=>0,'scale'=>1,'data'=>$array['logistic_code']], // 条形码,

            ['type' => 'TEXT','start_x'=>14,'start_y'=>1340,'font_size'=>14,'row_height'=>0,'row_count'=>0,'text'=>'寄件人：'.$array['sender_name']], // 寄件人姓名
            ['type' => 'TEXT','start_x'=>14,'start_y'=>1362,'font_size'=>14,'row_height'=>0,'row_count'=>0,'text'=>'寄件人电话：'.$array['sender_mobile']], // 寄件人电话
            ['type' => 'TEXT','start_x'=>14,'start_y'=>1384,'font_size'=>14,'row_height'=>0,'row_count'=>0,'text'=>'寄件人公司：'.$array['sender_company']], // 寄件人公司
            ['type' => 'TEXT','start_x'=>14,'start_y'=>1406,'font_size'=>14,'row_height'=>25,'row_count'=>24,'text'=>'寄件人地址：'.$array['consignor_address']], // 寄件人地址

            ['type' => 'TEXT','start_x'=>14,'start_y'=>1460,'font_size'=>14,'row_height'=>0,'row_count'=>0,'text'=>'收件人：'.$data['receiver_name']], // 收件人姓名
            ['type' => 'TEXT','start_x'=>14,'start_y'=>1485,'font_size'=>14,'row_height'=>0,'row_count'=>0,'text'=>'收件人电话：'.$data['receiver_mobile']], // 收件人电话
            ['type' => 'TEXT','start_x'=>14,'start_y'=>1510,'font_size'=>14,'row_height'=>0,'row_count'=>0,'text'=>'收件人公司：'.$data['receiver_company']], // 收件人公司
            ['type' => 'TEXT','start_x'=>14,'start_y'=>1535,'font_size'=>14,'row_height'=>25,'row_count'=>24,'text'=>'收件人地址：'.$array['consignee_address']], // 收件人地址

        ];
        return ImageTemplateService::createFromPic($array['eps_shipper_code'],$array['logistic_code'],$back_file_path,$template_data,false);
    }
    //////////////////////////////////////////////////////////////////////////////////////////////////////////

    //////////////////////////////////////////////////////////////////////////////////////////////////////////
    public function test($data){
        $back_pic='yd201.jpg';
        $data=TemplateHelper::arrangeData($data);
        return self::createPic($data,'cntiantian.cloud',$back_pic);
    }
    public function createPic($data,$params,$json_file,$back_pic){
        $save_image_dir = SITE_PATH."/data/print_image/".date('Y-m-d')."/";
        $temp_array=self::json2Array(SITE_PATH.'/data/ExpressImage/template/cloud/'.$json_file);
        $fileName = $data['logistic_code'] ? $data['logistic_code'].'.png': 'template.png';

        $param_array=array_merge($temp_array['param'],$params);
//        if (file_exists($save_image_dir.$fileName)) {
//            return $save_image_dir.$fileName;
//        }
        $font_file = SITE_PATH."/data/font/msyh.ttf";
        $font_file1 = SITE_PATH."/data/font/msyhbd.ttc";//TMBGSTD.TTF";
        $express_image_dir = SITE_PATH.'/data/ExpressImage/';

        //从文件中读取背景
        $img=imagecreatefromjpeg(SITE_PATH."data/ExpressImage/template/".$param_array['back_pic']);

//        //创建一个颜色
        $blackColor = imagecolorallocate($img, 0, 0, 0);
        $blackColor1 = imagecolorallocate($img, 128, 128, 128);

        foreach ($temp_array['data'] as $key => $value){
            $start_X=$value['start_x'];
            $start_y=$value['start_y'];
            $col=$value['color'];
            $style=$value['style'];
            $type = $value['type'];
            switch ($type) {
                case 'GRAY':
                    $text =trim($data[$value['key']]);
                    while(strlen($text)>0){
                        $digital=substr($text,0,1);
                        $text=substr($text,1);
                        $src_path=SITE_PATH."data/ExpressImage/digital/".$digital.".png";
                        $src = imagecreatefromstring(file_get_contents($src_path));
                        imagecopy($img, $src, $start_X, $start_y, 0, 0, 120, 172);
                        $start_X=$start_X+150;
                    }
                    break;
                case 'TEXT':
                        $text = $value['text'].$data[$value['key']];
                        $fontSize = $value['font_size'];
                        $row_height = $value['row_height'];
                        $row_count = $value['row_count'];
                        if ($row_count > 0) {  //多行处理
                            $str_array = TemplateHelper::splitString($text, $row_count);  //文本处理成多行
                            $index = 0;
                            foreach ($str_array as $str) {
                                $start_y = $value['start_y'] + $row_height * $index;
                                imagettftext($img, $fontSize, 0, $start_X, $start_y, $col=='gray'?$blackColor1:$blackColor, $style=='bold'?$font_file1:$font_file, $str);
                                $index++;
                            }
                        } else {
                            imagettftext($img, $fontSize, 0, $start_X, $start_y, $col=='gray'?$blackColor1:$blackColor,$style=='bold'?$font_file1: $font_file, $text);
                        }
                    break;
                case 'MULTI':

                    break;
                case 'BARCODE':
                    $dst_w = $value['width'];
                    $dst_h = $value['height'];
                    $scale = empty($value['scale']) ? 3 : $value['scale'];
                    $thickness = empty($value['thickness']) ? 35 : $value['thickness'];
                    $font = isset($value['font']) ? $value['font'] : 1;
                    $code_data = $data[$value['key']];
                    $draw = ImageTemplateService::creatBarcode128($code_data, $scale, $thickness, $font);
                    $barCodeImage = $draw->get_im();
                    imagecopyresized($img, $barCodeImage, $start_X, $start_y, 0, 0, $dst_w, $dst_h, imagesx($barCodeImage), imagesy($barCodeImage));
                    break;
                case 'IMAGE':
                    $dst_w = $value['width'];
                    $dst_h = $value['height'];
                    $im_new = imagecreatefromjpeg($value['pci_path']);//返回图像标识符jpg
                    imagecopyresized($img, $im_new, $start_X, $start_y, 0, 0, $dst_w, $dst_h, imagesx($im_new), imagesy($im_new));
                    break;
                case 'QrCode':
                    $text = $value['text'];
                    if (empty($text))
                        break;
                    $dst_w = $value['width'];
                    $qr_code = QrCodeService::CreateQrCode($text);
                    imagecopyresized($img, $qr_code, $start_X, $start_y, 0, 0, $dst_w, $dst_w, imagesx($qr_code), imagesy($qr_code));
                    break;
                case 'Logo':
                    $dst_w = $value['width'];
                    $dst_h = $value['height'];
                    $position = $value['position'];
                    $flag=false;
                    if($position='up'&&$param_array['eps_up_logo']==1){
                        $flag=true;
                        $param_array['eps_up_logo']='';
                    }else{
                        switch ($param_array['eps_down_logo']){
                            case 0:
                                break;
                            case 1:
                                $flag=true;
                            case 2:
                                $start_X=$param_array['width']-$dst_w-10;
                                break;
                        }
                    }
                    if($flag) {
                        $im_new = imagecreatefromjpeg($express_image_dir . $data['logistic_code'] . ".jpg");//返回图像标识符
                        imagecopyresized($img, $im_new, $start_X, $start_y, 0, 0, $dst_w, $dst_h, imagesx($im_new), imagesy($im_new));
                    }
                    break;
            }
        }

        //根据参数作设置

        if(!file_exists($save_image_dir)) {
            //可创建多级目录
            mkdir($save_image_dir);
            chmod($save_image_dir,0777);
            // mkdir($save_image_dir,0777,true);
        }
        //输出图像到网页(或者另存为)
        //ImageTemplateService::imageGreyscale($img,0);
        if($data['$is_flip180']){
            $img=imagerotate($img,180,0);
        }
        imagepng($img,$save_image_dir.$fileName,9);
        imagedestroy($img);
        return $save_image_dir.$fileName;
    }

    public function json2Array($json_file){
        $json_data=file_get_contents($json_file);
        $array=json_decode($json_data,true);
        return $array;
    }
    /**
     * 通用生成图片方法
     * @param $shipper_code
     * @param $logistic_code
     * @param $back_file_path
     * @param $template_data
     * @param bool $is_flip180
     * @return string
     */
    public static function createFromPic($shipper_code,$logistic_code,$back_file_path,$template_data,$is_flip180=false){

        $save_image_dir = SITE_PATH."/data/TempletImage/".date('Y-m-d')."/";
        $fileName = 'template.png';
        $fileName = $logistic_code ? $logistic_code.'.png': $fileName;

        if (file_exists($save_image_dir.$fileName)) {
            return $save_image_dir.$fileName;
        }
        $font_file = SITE_PATH."/data/font/msyh.ttf";
        $font_file1 = SITE_PATH."/data/font/msyhbd.ttc";//TMBGSTD.TTF";
        $express_image_dir = SITE_PATH.'/data/ExpressImage/';

        //从文件中读取背景
        $img=imagecreatefromjpeg(SITE_PATH."data/ExpressImage/template/".$back_file_path);


//        //创建一个颜色
        $blackColor = imagecolorallocate($img, 0, 0, 0);
        $blackColor1 = imagecolorallocate($img, 128, 128, 128);

        foreach ($template_data as $key => $value){
            $start_X=$value['start_x'];
            $start_y=$value['start_y'];
            $col=$value['color'];
            $style=$value['style'];
                $type = $value['type'];
                switch ($type) {
                    case 'TEXT':
                        if($col=='gray'){
                            $text =trim($value['text']);
                            while(strlen($text)>0){
                                $digital=substr($text,0,1);
                                $text=substr($text,1);
                                $src_path=SITE_PATH."data/ExpressImage/digital/".$digital.".png";
                                $src = imagecreatefromstring(file_get_contents($src_path));
                                imagecopy($img, $src, $start_X, $start_y, 0, 0, 120, 172);
                                $start_X=$start_X+150;
                            }
                        }
                        else{
                            $text = $value['text'];
                            $fontSize = $value['font_size'];
                            $row_height = $value['row_height'];
                            $row_count = $value['row_count'];
                            if ($row_count > 0) {  //多行处理
                                $str_array = TemplateHelper::splitString($text, $row_count);  //文本处理成多行
                                $index = 0;
                                foreach ($str_array as $str) {
                                    $start_y = $value['start_y'] + $row_height * $index;
                                    imagettftext($img, $fontSize, 0, $start_X, $start_y, $col=='gray'?$blackColor1:$blackColor, $style=='bold'?$font_file1:$font_file, $str);
                                    $index++;
                                }
                            } else {
                                imagettftext($img, $fontSize, 0, $start_X, $start_y, $col=='gray'?$blackColor1:$blackColor,$style=='bold'?$font_file1: $font_file, $text);
                            }
                        }
                        break;
                    case 'BARCODE':
                        $dst_w = $value['width'];
                        $dst_h = $value['height'];
                        $scale = empty($value['scale']) ? 3 : $value['scale'];
                        $thickness = empty($value['thickness']) ? 35 : $value['thickness'];
                        $font = isset($value['font']) ? $value['font'] : 1;
                        $code_data = $value['data'];
                        try {
                            $draw = ImageTemplateService::creatBarcode128($code_data, $scale, $thickness, $font);
                            $barCodeImage = $draw->get_im();
                            imagecopyresized($img, $barCodeImage, $start_X, $start_y, 0, 0, $dst_w, $dst_h, imagesx($barCodeImage), imagesy($barCodeImage));
                        }catch (\Exception  $e){
                            \Think\Log::record($e->getMessage());
                        }
                        break;
                    case 'IMAGE':
                        $dst_w = $value['width'];
                        $dst_h = $value['height'];
                        $im_new = imagecreatefromjpeg($express_image_dir . $shipper_code . ".jpg");//返回图像标识符
                        imagecopyresized($img, $im_new, $start_X, $start_y, 0, 0, $dst_w, $dst_h, imagesx($im_new), imagesy($im_new));
                        break;
                    case 'QrCode':
                        $text = $value['text'];
                        if (empty($text))
                            break;
                        $dst_w = $value['width'];
                        $qr_code = QrCodeService::CreateQrCode($text);
                        imagecopyresized($img, $qr_code, $start_X, $start_y, 0, 0, $dst_w, $dst_w, imagesx($qr_code), imagesy($qr_code));
                        break;
                }
        }
        if(!file_exists($save_image_dir)) {
            //可创建多级目录
            mkdir($save_image_dir);
            chmod($save_image_dir,0777);
            // mkdir($save_image_dir,0777,true);
        }
        //输出图像到网页(或者另存为)
        //ImageTemplateService::imageGreyscale($img,0);
        if($is_flip180){
            $img=imagerotate($img,180,0);
        }
        imagepng($img,$save_image_dir.$fileName,9);
        imagedestroy($img);
        return $save_image_dir.$fileName;
    }


    /**
     * 图片灰度处理
     * @param $img
     * @param int $dither
     */
    public static   function imageGreyscale(&$img, $dither=1) {
        if (!($t = imagecolorstotal($img))) {
            $t = 256;
            imagetruecolortopalette($img, $dither, $t);
        }
        for ($c = 0; $c < $t; $c++) {
            $col = imagecolorsforindex($img, $c);
            $min = min($col['red'],$col['green'],$col['blue']);
            $max = max($col['red'],$col['green'],$col['blue']);
            $i = ($max+$min)/2>50?($max+$min)/2-50:0;
            if($i>130)
                imagecolorset($img, $c, 255, 255, 255);
            else
                imagecolorset($img, $c, $i, $i, $i);

        }
    }
    /**
     * [sstrlen 计算UNIcode字符长度]
     */
    public  static function sstrlen($str) {
        $n = 0; $p = 0; $c = '';
        $len = strlen($str);
        for($i = 0; $i < $len; $i++) {
            $c = ord($str{$i});
            if($c > 252) {
                $p = 5;
                $n += 2;
            } elseif($c > 248) {
                $p = 4;
                $n += 2;
            } elseif($c > 240) {
                $p = 3;
                $n += 2;
            } elseif($c > 224) {
                $p = 2;
                $n += 2;
            } elseif($c > 192) {
                $p = 1;
                $n += 1;
            } else {
                $p = 0;
                $n += 1;
            }
            $i+=$p;
        }

        return $n;
    }
}