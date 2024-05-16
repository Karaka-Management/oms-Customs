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

$codes = $this->data['codes'] ?? [];
?>
<div class="row">
    <div class="col-xs-12">
        <section class="portlet">
            <div class="portlet-head simple-flex">
                <?= $this->getHtml('HSCodes'); ?><i class="g-icon download btn end-xs">download</i>
                <form id="fHSCodeList" method="GET" action="<?= UriFactory::build('{/base}/customs/hscode/dashboard?{?}&csrf={$CSRF}'); ?>">
                    <span role="search" class="inputWrapper">
                        <span class="txtWrap">
                            <input id="iHSCodeSearch" name="hscode" type="text" autocomplete="off" value="<?= $this->request->getDataString('hscode') ?? ''; ?>" autofocus>
                        </span>
                        <a class="button" href="<?= UriFactory::build('{/base}/customs/hscode/dashboard?{?}&csrf={$CSRF}'); ?>&hscode={#iHSCodeSearch}"><?= $this->getHtml('Search', '0', '0'); ?></a>
                    </span>
                </form>
            </div>
            <div class="slider">
            <table class="default sticky">
            <thead>
            <tr>
                <td><?= $this->getHtml('Code'); ?>
                <td class="wf-100"><?= $this->getHtml('Description'); ?>
            <tbody>
            <?php $count = 0;
            foreach ($codes as $key => $value) : ++$count;
                $id           = \substr($value['Goods_code'], 0, (int) \stripos($value['Goods_code'], ' '));
                $url          = UriFactory::build('{/base}/customs/hscode/view?id=' . $id);
                $description  = $this->printHtml($value['Description']);
                $foundKeyword = \stripos($description, $this->request->getDataString('hscode'));

                if ($foundKeyword !== false) {
                    $len = \strlen($this->request->getDataString('hscode'));

                    // Whitespace handling is causing issues since the whitespace is only rendered if followed by another character
                    // If whitespace found -> exclude from string -> add &nbsp; which gets always rendered unlike the whitespace
                    $startsWithS = ($description[$foundKeyword - 1] ?? '') === ' ';
                    $endsWithS   = ($description[$foundKeyword + $len] ?? '') === ' ';

                    $description = \substr($description, 0, $foundKeyword - ((int) $startsWithS))
                        . (($description[$foundKeyword - 1] ?? '') === ' ' ? '&nbsp;' : '')
                        . '<mark>' . \substr($description, $foundKeyword, $len) . '</mark>'
                        . (($description[$foundKeyword + $len] ?? '') === ' ' ? '&nbsp;' : '')
                        . \substr($description, $foundKeyword + $len + ((int) $endsWithS));
                }
            ?>
                <tr tabindex="0" data-href="<?= $url; ?>">
                    <td data-label="<?= $this->getHtml('Code'); ?>"><a href="<?= $url; ?>"><?= $this->printHtml($value['Indent']); ?><?= $this->printHtml($value['Goods_code']); ?></a>
                    <td data-label="<?= $this->getHtml('Description'); ?>"><a href="<?= $url; ?>"><?= $description; ?></a>

            <?php endforeach; ?>
            <?php if ($count === 0) : ?>
                <tr><td colspan="3" class="empty"><?= $this->getHtml('Empty', '0', '0'); ?>
            <?php endif; ?>
            </table>
        </section>
    </div>
</div>
