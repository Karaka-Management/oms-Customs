<?php
/**
 * Jingga
 *
 * PHP Version 8.2
 *
 * @package   Modules\Customs
 * @copyright Dennis Eichhorn
 * @license   OMS License 2.0
 * @version   1.0.0
 * @link      https://jingga.app
 */
declare(strict_types=1);

use phpOMS\Uri\UriFactory;

$sanctions = $this->data['sanctions'] ?? [];
?>
<div class="row">
    <div class="col-xs-12 col-md-6">
        <section class="portlet">
            <form id="fSanctionList" method="GET_REDIRECT" action="<?= UriFactory::build('{/base}/customs/sanction/dashboard?{?}&csrf={$CSRF}'); ?>">
            <div class="portlet-head"><?= $this->getHtml('Basic'); ?></div>
            <div class="portlet-body">
                <div class="form-group">
                    <label for="iName"><?= $this->getHtml('Name'); ?></label>
                    <input type="text" id="iName" name="name" value="<?= $this->printHtml($this->request->getDataString('name')); ?>">
                </div>

                <div class="form-group">
                    <label for="iAddress"><?= $this->getHtml('Address'); ?></label>
                    <input type="text" id="iAddress" name="address" value="<?= $this->printHtml($this->request->getDataString('address')); ?>">
                </div>

                <div class="form-group">
                    <label for="iCity"><?= $this->getHtml('City'); ?></label>
                    <input type="text" id="iCity" name="city" value="<?= $this->printHtml($this->request->getDataString('city')); ?>">
                </div>

                <div class="form-group">
                    <label for="iCountry"><?= $this->getHtml('Country'); ?></label>
                    <input type="text" id="iCountry" name="country" value="<?= $this->printHtml($this->request->getDataString('country')); ?>">
                </div>
            </div>
            <div class="portlet-foot">
                <input type="submit" id="iSubmit" value="<?= $this->getHtml('Search', '0', '0'); ?>">
            </div>
            </form>
        </section>
    </div>

    <div class="col-xs-12 col-md-6">
        <section class="portlet">
            <div class="portlet-head"><?= $this->getHtml('Detail'); ?></div>
            <div class="portlet-body">
                <div class="form-group">
                    <label for="iBirthday"><?= $this->getHtml('Birthday'); ?></label>
                    <input type="date" form="fSanctionList" id="iBirthday" name="birthday" value="<?= $this->printHtml($this->request->getDataString('birthday')); ?>">
                </div>

                <div class="form-group">
                    <label for="iIdentificationNumber"><?= $this->getHtml('IdentificationNumber'); ?></label>
                    <input type="text" form="fSanctionList" id="iIdentificationNumber" name="identno" value="<?= $this->printHtml($this->request->getDataString('identno')); ?>">
                </div>
            </div>
        </section>
    </div>
</div>

<div class="row">
    <div class="col-xs-12">
        <section class="portlet">
            <div class="portlet-head">
                <?= $this->getHtml('Sanctions'); ?><i class="g-icon download btn end-xs">download</i>
            </div>
            <div class="slider">
            <table class="default sticky">
            <thead>
            <tr>
                <td><?= $this->getHtml('Type'); ?>
                <td><?= $this->getHtml('Name'); ?>
                <td><?= $this->getHtml('Address'); ?>
                <td><?= $this->getHtml('City'); ?>
                <td><?= $this->getHtml('Country'); ?>
                <td class="wf-100"><?= $this->getHtml('Remark'); ?>
            <tbody>
            <?php $count = 0;
            foreach ($sanctions as $key => $value) : ++$count;
                //$url = UriFactory::build('{/base}/customs/sanction/view?{?}&id=' . $value['Ent_num']);
            ?>
                <tr tabindex="0">
                    <td data-label="<?= $this->getHtml('Type'); ?>"><?= $this->printHtml($value['sanction_db']); ?>
                    <?php if (isset($value['parsed'])) : ?>
                    <td colspan="5"><?= $this->printHtml($value['parsed']); ?>
                    <?php else : ?>
                    <td data-label="<?= $this->getHtml('Name'); ?>"><?= $this->printHtml($value['SDN_name']); ?>; <?= $this->printHtml($value['alt_name']); ?>
                    <td data-label="<?= $this->getHtml('Address'); ?>"><?= $this->printHtml($value['Address']); ?>
                    <td data-label="<?= $this->getHtml('City'); ?>"><?= $this->printHtml($value['City_Province_PostalCode']); ?>
                    <td data-label="<?= $this->getHtml('Country'); ?>"><?= $this->printHtml($value['Country']); ?>
                    <td data-label="<?= $this->getHtml('Remark'); ?>"><?= $this->printHtml($value['Remarks']); ?>
                    <?php endif; ?>
            <?php endforeach; ?>
            <?php if ($count === 0) : ?>
                <tr><td colspan="6" class="empty"><?= $this->getHtml('Empty', '0', '0'); ?>
            <?php endif; ?>
            </table>
        </section>
    </div>
</div>
