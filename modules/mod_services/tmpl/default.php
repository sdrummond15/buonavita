<?php
$items = $params->get('items');
if (!empty($items)) :
?>
    <div id="modservices">

            <?php
            $count = 1;
            $r=22;
            $g=55;
            $b=42;
            $countArray = count(get_object_vars($items));

            foreach ($items as $key => $item) :
                $link = JRoute::_('index.php?Itemid=' . $item->menu);
            ?>
                <?php if($count%3 == 1):?>
                <div class="service-separator" 
                style="
                    background: rgb(<?= $r ?>, <?= $g ?>, <?= $b ?>);
                    <?php if($count != $countArray):?> 
                    background: -moz-linear-gradient(90deg, rgba(<?= $r ?>, <?= $g ?>, <?= $b ?>, 1) 50%, rgba(<?= $r+4 ?>, <?= $g+16 ?>, <?= $b+10 ?>, 1) 50%);
                    background: -webkit-linear-gradient(90deg, rgba(<?= $r ?>, <?= $g ?>, <?= $b ?>, 1) 50%, rgba(<?= $r+4 ?>, <?= $g+16 ?>, <?= $b+10 ?>, 1) 50%);
                    background: linear-gradient(90deg, rgba(<?= $r ?>, <?= $g ?>, <?= $b ?>, 1) 50%, rgba(<?= $r+4 ?>, <?= $g+16 ?>, <?= $b+10 ?>, 1) 50%);
                    filter: progid:DXImageTransform.Microsoft.gradient(startColorstr='#204134', endColorstr='#3c8267', GradientType=1);
                    <?php endif; ?>
                ">
                    <div class="services">
                <?php endif; ?>
                        <div class="service" style="background-color: rgb(<?= $r ?>, <?= $g ?>, <?= $b ?>);">
                            <div class="item-service">
                                <a href="<?= $link; ?>" class="img-service" style="background-image: url('<?= $item->icon ?>')">
                                </a>
                                <h1>
                                    <a href="<?= $link; ?>"><?= $item->title; ?></a>
                                </h1>
                            </div>
                        </div>
                <?php if($count%3 == 0 || $count == $countArray):?>
                    </div>
                </div>
                <?php endif; ?>
            <?php
                $r += 2;
                $g += 8;
                $b += 5;
                $count++;
            endforeach;
            ?>

    </div>
<?php endif; ?>