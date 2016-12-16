<?php
require('goods.class.php');
$config = include('config.php');
$goods = new goods($config);
$goodsInfo = $goods->goodsData();
#var_dump($goodsInfo);
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
        <title>名品网仓特卖</title>

        <!-- Bootstrap -->
        <link href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.7/css/bootstrap.min.css" rel="stylesheet">

        <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
        <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
        <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
        <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
        <![endif]-->
        <style type="text/css">
            .navbar-static-top {
                margin-bottom: 19px;
            }
            .navbar-header {
                color: #333;
            }
            .footer {
                position: absolute;
                bottom: 0;
                width: 100%;
                /* Set the fixed height of the footer here */
                height: 60px;
                background-color: #f5f5f5;
            }
            /* Sticky footer styles
            -------------------------------------------------- */
            html {
              position: relative;
              min-height: 100%;
            }
            body {
              /* Margin bottom by footer height */
              margin-bottom: 60px;
            }
            .footer {
              position: absolute;
              top: 60px;
              bottom: 0;
              width: 100%;
              /* Set the fixed height of the footer here */
              height: 60px;
              background-color: #f5f5f5;
            }


            /* Custom page CSS
            -------------------------------------------------- */
            /* Not required for template or sticky footer method. */

            body > .container {
              padding: 60px 15px 0;
            }
            .container .text-muted {
              margin: 20px 0;
            }

            .footer > .container {
              padding-right: 15px;
              padding-left: 15px;
            }

            code {
              font-size: 80%;
            }


        </style>
    </head>

    <body>
        <!-- Static navbar -->
        <nav class="navbar navbar-default navbar-static-top">
            <div class="container">
                <div class="navbar-header">
                    <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
                        <span class="sr-only">Toggle navigation</span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                    </button>
                    <a class="navbar-brand" href="#">名品</a>
                </div>
                <div id="navbar" class="navbar-collapse collapse">
                    <ul class="nav navbar-nav">
                        <li class="active"><a href="#">商品</a></li>
                        <li><a href="#about">报表</a></li>
                        <li><a href="#contact">入库</a></li>
                        <li><a href="#contact">出库</a></li>
                    </ul>
                </div><!--/.nav-collapse -->
            </div>
        </nav>


        <div class="container">
            <div class="jumbotron">
                <h4>联系方式：胡小姐</h4>
                <p></p>
            </div>
<?php
if(count($goodsInfo) > 0){
    foreach($goodsInfo as $val){
?>
            <h3><?php echo $val['goods_num'].' - '.$val['goods_desc']; ?></h3>
            <hr>
                <div class="row">
                    <div class="col-xs-8">
<?php
        if(count($val['img']) > 0){
            foreach($val['img'] as $img){
                if(strpos($img, '@eaDir') || strpos($img, '.DS_')){
                    continue;
                }
?>
                        <a href="####" class="" id="pop">
                            <img id="imageresource" src="img/<?php echo $val['goods_num'].''.$img; ?>" style="height: 110px;" alt="" class="img-rounded">
                        </a>
<?php
            }
        }
?>
                    </div>
                    <div class="col-xs-4">
                        <p><?php echo '['.$val['goods_sn'].']'; ?></p>
                        <p></p>
                        <p><?php echo $val['goods_material']; ?></p>
                        <p><?php echo '颜色：'.$val['goods_color']; ?></p>
                        <p>
                            原价：
                            <del><?php echo number_format($val['goods_price'], 2); ?></del>
                        </p>
                        <p>
                            <strong>名品折扣价：</strong>
                            <strong style="color:#F2265F">
                                <?php echo number_format(round($val['goods_price']*$val['goods_sale']), 2); ?>
                            </strong>
                        </p>
                    </div>
                </div>
<?php
    }
}
?>
            <!-- Creates the bootstrap modal where the image will appear -->
            <div class="modal fade" id="imagemodal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                            <h4 class="modal-title" id="myModalLabel">Image preview</h4>
                        </div>
                        <div class="modal-body">
                            <img src="" id="imagepreview" style="width: 400px;" >
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>
        </div> <!-- /container -->

        <!-- <footer class="footer">
            <div class="container">
                <p class="text-muted">@名品特卖</p>
            </div>
        </footer> -->


        <!-- IE10 viewport hack for Surface/desktop Windows 8 bug -->
        <!-- <script src="../../assets/js/ie10-viewport-bug-workaround.js"></script> -->

        <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/1.12.4/jquery.js"></script>
        <!-- Include all compiled plugins (below), or include individual files as needed -->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.7/js/bootstrap.min.js"></script>
        <script>
            $(function(){
                $("a#pop").on("click", function() {
                    //console.log($(this).children("#imageresource").attr('src'));
                    $('#imagepreview').attr('src', $(this).children("#imageresource").attr('src')); // here asign the image to the modal when the user click the enlarge link
                    $('#imagemodal').modal('show'); // imagemodal is the id attribute assigned to the bootstrap modal, then i use the show function
                });
            });
        </script>
    </body>
</html>
