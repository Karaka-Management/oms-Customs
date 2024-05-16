<?php
$footnotes = $this->data['footnotes'] ?? [];
$goods     = $this->data['goods'] ?? [];
$baseDate  = \strtotime("1899-12-30");
?>
<div class="row">
    <div class="col-xs-12 col-md-6">
        <section class="portlet">
            <div class="portlet-body">
            <?php foreach ($goods as $good) : ?>
                <div class="form-group">
                    <label><?= $good['Goods_code']; ?></label>
                    <p><?= $good['Description']; ?></p>
                </div>
            <?php endforeach; ?>
            </div>
        </section>
    </div>
</div>

<?php
$length     = \count($footnotes);
$lastOrigin = '';
foreach ($footnotes as $idx => $value) :
    if ($lastOrigin === $value['Origin_code']) {
        continue;
    }
?>
<div class="row">
    <div class="col-xs-12">
        <section class="portlet more-container">
            <input id="more-settings-<?= $idx; ?>" class="more" type="checkbox" name="more-container">
            <div class="portlet-head">
                <label class="more" for="more-settings-<?= $idx; ?>">
                    <span><?= $this->printHtml($value['Origin_code']); ?> - <?= $this->printHtml($value['Origin']); ?></span>
                    <i class="g-icon expand">chevron_right</i>
                </label><i class="g-icon download btn end-xs">download</i>
            </div>
            <div class="more">
            <div>
            <?php for ($i = $idx; $i < $length; ++$i) :
                if ($value['Origin_code'] !== $footnotes[$i]['Origin_code']) {
                    break;
                }

                $start = '';
                if (!empty($footnotes[$i]['Start_date'])) {
                    $seconds = $footnotes[$i]['Start_date'] * 86400;
                    $date    = new DateTime('@' . ($baseDate + $seconds));
                    $start   = $date->format('Y-m-d');
                }
            ?>
            <div class="portlet-body">
                <div class="form-group">
                    <label><?= $this->getHtml('Start'); ?></label>
                    <p><?= $start; ?></p>
                </div>

                <div class="form-group">
                    <label><?= $this->getHtml('MeasureType'); ?></label>
                    <p><?= $this->printHtml($footnotes[$i]['Meas_type_code']); ?> - <?= $this->printHtml($footnotes[$i]['Measure_type']); ?></p>
                </div>

                <?php if (!empty($footnotes[$i]['Add_code'])) : ?>
                <div class="form-group">
                    <label><?= $this->getHtml('AddCode'); ?></label>
                    <p><?= $this->printHtml($footnotes[$i]['Add_code']); ?> - <?= $this->printHtml($footnotes[$i]['Add_Description']); ?></p>
                </div>
                <?php endif; ?>

                <?php if (!empty($footnotes[$i]['Footnote'])) : ?>
                <div class="form-group">
                    <label><?= $this->getHtml('Footnote'); ?></label>
                    <p><?= $this->printHtml($footnotes[$i]['Footnote']); ?> - <?= $this->printHtml($footnotes[$i]['Footnote_Description']); ?></p>
                </div>
                <?php endif; ?>

                <?php if (!empty($footnotes[$i]['Import_Duty'])) : ?>
                <div class="form-group">
                    <label><?= $this->getHtml('ImportDuty'); ?></label>
                    <p><?= $this->printHtml($footnotes[$i]['Import_Duty']); ?></p>
                </div>
                <?php endif; ?>

                <?php if (!empty($footnotes[$i]['Export_Duty'])) : ?>
                <div class="form-group">
                    <label><?= $this->getHtml('ExportDuty'); ?></label>
                    <p><?= $this->printHtml($footnotes[$i]['Export_Duty']); ?></p>
                </div>
                <?php endif; ?>
            </div>
            <div class="portlet-separator"></div>
            <?php endfor; ?>
            </div></div>
        </section>
    </div>
</div>
<?php
$lastOrigin = $value['Origin_code'];
endforeach;
?>
