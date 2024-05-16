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

namespace Modules\Customs\Controller;

use phpOMS\Contract\RenderableInterface;
use phpOMS\DataStorage\Database\Query\Builder;
use phpOMS\Message\RequestAbstract;
use phpOMS\Message\ResponseAbstract;
use phpOMS\System\File\SearchUtils;
use phpOMS\Views\View;

/**
 * Customs class.
 *
 * @package Modules\Customs
 * @license OMS License 2.0
 * @link    https://jingga.app
 * @since   1.0.0
 * @codeCoverageIgnore
 */
final class BackendController extends Controller
{
    /**
     * Backend method to handle basic search request
     *
     * @param RequestAbstract  $request  Request
     * @param ResponseAbstract $response Response
     * @param array            $data     Generic data
     *
     * @return RenderableInterface
     *
     * @api
     *
     * @since 1.0.0
     */
    public function viewSanctionView(RequestAbstract $request, ResponseAbstract $response, array $data = []) : RenderableInterface
    {
        $view = new View($this->app->l11nManager, $request, $response);
        $view->setTemplate('/Modules/Customs/Theme/Backend/sanction-view');

        return $view;
    }

    /**
     * Backend method to handle basic search request
     *
     * @param RequestAbstract  $request  Request
     * @param ResponseAbstract $response Response
     * @param array            $data     Generic data
     *
     * @return RenderableInterface
     *
     * @api
     *
     * @since 1.0.0
     */
    public function viewSanctionDashboard(RequestAbstract $request, ResponseAbstract $response, array $data = []) : RenderableInterface
    {
        $view = new View($this->app->l11nManager, $request, $response);
        $view->setTemplate('/Modules/Customs/Theme/Backend/sanction-dashboard');

        $view->data['sanctions'] = [];

        if (!$request->hasData('name')) {
            return $view;
        }

        $con = new \phpOMS\DataStorage\Database\Connection\SQLiteConnection([
            'db'       => 'sqlite',
            'database' => __DIR__ . '/../Data/customs.sqlite',
        ]);

        $con->connect();

        $query = new Builder($con);

        $nameString      = \str_replace(['.', ';', ',', '-'], ' ', $request->getDataString('name') ?? '');
        $names           = \explode(' ', $nameString);
        $fileSearchArray = $names;
        $name            = '';

        // US SDN and CONS
        foreach ($names as $idx => $n) {
            if (empty($n)) {
                continue;
            }

            $name .= ' AND (SDN_name LIKE :name' . $idx . ' OR alt_name LIKE :name' . $idx . ')';
            $query->bind(['value' => '%' . $n . '%'], ':name'. $idx);
        }

        $name = \ltrim($name, ' AND');

        $address = '';
        if ($request->hasData('address')) {
            $address = 'AND (Address LIKE :address OR Address LIKE "%-0-%")';
            $query->bind(['value' => '%' . $request->getDataString('address') . '%'], ':address');
            $fileSearchArray[] = $request->getDataString('address');
        }

        $city = '';
        if ($request->hasData('city')) {
            $city = 'AND (City_Province_PostalCode LIKE :city OR City_Province_PostalCode LIKE "%-0-%")';
            $query->bind(['value' => '%' . $request->getDataString('city') . '%'], ':city');
            $fileSearchArray[] = $request->getDataString('city');
        }

        $country = '';
        if ($request->hasData('country')) {
            $country = 'AND (Country LIKE :country OR Country LIKE "%-0-%")';
            $query->bind(['value' => '%' . $request->getDataString('country') . '%'], ':country');
            $fileSearchArray[] = $request->getDataString('country');
        }

        $sql = <<<SQL
        SELECT sanction_us_sdn.Ent_num,
            sanction_us_sdn.SDN_name, sanction_us_sdn_alt.alt_name,
            sanction_us_sdn_add.Address, sanction_us_sdn_add.City_Province_PostalCode, sanction_us_sdn_add.Country,
            sanction_us_sdn.Remarks,
            'US_SDN' as sanction_db
        FROM sanction_us_sdn
        LEFT JOIN sanction_us_sdn_alt ON sanction_us_sdn.Ent_num = sanction_us_sdn_alt.Ent_num
        LEFT JOIN sanction_us_sdn_add ON sanction_us_sdn.Ent_num = sanction_us_sdn_add.Ent_num
        WHERE {$name}
            {$address}
            {$city}
            {$country}
        LIMIT 100;
        SQL;

        $view->data['sanctions'] = \array_merge($view->data['sanctions'], $query->raw($sql)->execute()?->fetchAll() ?? []);

        $sql = <<<SQL
        SELECT sanction_us_cons.Ent_num,
            sanction_us_cons.SDN_name, sanction_us_cons_alt.alt_name,
            sanction_us_cons_add.Address, sanction_us_cons_add.City_Province_PostalCode, sanction_us_cons_add.Country,
            sanction_us_cons.Remarks,
            'US_CONS' as sanction_db
        FROM sanction_us_cons
        LEFT JOIN sanction_us_cons_alt ON sanction_us_cons.Ent_num = sanction_us_cons_alt.Ent_num
        LEFT JOIN sanction_us_cons_add ON sanction_us_cons.Ent_num = sanction_us_cons_add.Ent_num
        WHERE {$name}
            {$address}
            {$city}
            {$country}
        LIMIT 100;
        SQL;

        $view->data['sanctions'] = \array_merge($view->data['sanctions'], $query->raw($sql)->execute()?->fetchAll() ?? []);

        // EU Consolidated
        $name = '';

        foreach ($names as $idx => $n) {
            if (empty($n)) {
                continue;
            }

            $name .= ' AND (sanction_eu_cons.NameAlias_WholeName LIKE :name' . $idx . ' OR sanction_eu_cons.NameAlias_Title LIKE :name' . $idx . ')';
            $query->bind(['value' => '%' . $n . '%'], ':name' . $idx);
        }

        $name = \ltrim($name, ' AND');

        $address = '';
        if ($request->hasData('address')) {
            $address = 'AND (address_subquery.Address LIKE :address OR address_subquery.Address = "")';
            $query->bind(['value' => '%' . $request->getDataString('address') . '%'], ':address');
        }

        $city = '';
        if ($request->hasData('city')) {
            $city = 'AND (address_subquery.City_Province_PostalCode LIKE :city OR address_subquery.City_Province_PostalCode = "")';
            $query->bind(['value' => '%' . $request->getDataString('city') . '%'], ':city');
        }

        $country = '';
        if ($request->hasData('country')) {
            $country = 'AND (address_subquery.Country LIKE :country OR address_subquery.Country = "")';
            $query->bind(['value' => '%' . $request->getDataString('country') . '%'], ':country');
        }

        $birthday = '';
        if ($request->hasData('birthday')) {
            $birthday = 'AND (DATE(sanction_eu_cons.BirthDate_BirthDate) = DATE(:birthday) OR sanction_eu_cons.BirthDate_BirthDate = "" OR DATE(address_subquery.BirthDate_BirthDate) = DATE(:birthday) OR address_subquery.BirthDate_BirthDate = "")';
            $query->bind(['value' => $request->getDataString('birthday')], ':birthday');
        }

        $identno = '';
        if ($request->hasData('identno')) {
            $identno = 'AND (sanction_eu_cons.Identification_Number LIKE :identno OR sanction_eu_cons.Identification_Number = "" OR address_subquery.Identification_Number LIKE :identno OR address_subquery.Identification_Number = "")';
            $query->bind(['value' => '%' . $request->getDataString('identno') . '%'], ':identno');
        }

        $sql = <<<SQL
        SELECT
            sanction_eu_cons.Entity_LogicalId as Ent_num,
            sanction_eu_cons.NameAlias_WholeName AS SDN_name,
            sanction_eu_cons.NameAlias_Title AS alt_name,
            address_subquery.Address AS Address,
            address_subquery.City_Province_PostalCode AS City_Province_PostalCode,
            address_subquery.Country AS Country,
            sanction_eu_cons.Entity_Remark AS Remarks,
            'EU_CONS' AS sanction_db
        FROM sanction_eu_cons
        LEFT JOIN
            (
                SELECT
                    Entity_LogicalId,
                    Address_Street AS Address,
                    Address_City AS City_Province_PostalCode,
                    Address_CountryDescription AS Country
                FROM
                    sanction_eu_cons
                WHERE
                    Address_Street <> "" OR Address_City <> "" OR Address_CountryDescription <> ""
            ) AS address_subquery
        ON
            sanction_eu_cons.Entity_LogicalId = address_subquery.Entity_LogicalId
        WHERE {$name}
            {$address}
            {$city}
            {$country}
            {$birthday}
            {$identno}
        GROUP BY sanction_eu_cons.Entity_LogicalId,
            sanction_eu_cons.NameAlias_WholeName,
            sanction_eu_cons.NameAlias_Title,
            address_subquery.Address,
            address_subquery.City_Province_PostalCode,
            address_subquery.Country,
            sanction_eu_cons.Entity_Remark,
            'EU_CONS'
        LIMIT 500;
        SQL;

        $view->data['sanctions'] = \array_merge($view->data['sanctions'], $query->raw($sql)->execute()?->fetchAll() ?? []);

        $con->close();

        // EU 881/2002
        $positions = SearchUtils::findInFile(__DIR__ . '/../Data/Sanctions/EU/CELEX 32002R0881 EN TXT.html', $fileSearchArray);

        $lex_881_2002 = [];
        $hashResults  = [];
        foreach ($positions as $position) {
            if ($position['distance'] > 500) {
                continue;
            }

            $sanction = \trim(\strip_tags(
                SearchUtils::getTextExtract(
                    __DIR__ . '/../Data/Sanctions/EU/CELEX 32002R0881 EN TXT.html',
                    $position['start'],
                    '<p', '</p>'
                )
            ));

            if (\in_array($hash = \sha1($sanction), $hashResults)) {
                continue;
            }

            $hashResults[]  = $hash;
            $lex_881_2002[] = [
                'sanction_db' => 'EU_881/2002',
                'Ent_num'     => 'EU_881/2002',
                'parsed'      => $sanction,
            ];
        }

        $view->data['sanctions'] = \array_merge($view->data['sanctions'], $lex_881_2002);

        // EU 753/2011
        $positions = SearchUtils::findInFile(__DIR__ . '/../Data/Sanctions/EU/CELEX 32011R0753 EN TXT.html', $fileSearchArray);

        $lex_753_2011 = [];
        $hashResults  = [];
        foreach ($positions as $position) {
            if ($position['distance'] > 500) {
                continue;
            }

            $sanction = \trim(\strip_tags(
                SearchUtils::getTextExtract(
                    __DIR__ . '/../Data/Sanctions/EU/CELEX 32011R0753 EN TXT.html',
                    $position['start'],
                    '<p', '</p>'
                )
            ));

            if (\in_array($hash = \sha1($sanction), $hashResults)) {
                continue;
            }

            $hashResults[]  = $hash;
            $lex_753_2011[] = [
                'sanction_db' => 'EU_753/2011',
                'Ent_num'     => 'EU_753/2011',
                'parsed'      => $sanction,
            ];
        }

        $view->data['sanctions'] = \array_merge($view->data['sanctions'], $lex_753_2011);

        // EU 2024/385
        $positions = SearchUtils::findInFile(__DIR__ . '/../Data/Sanctions/EU/CELEX 32011R0753 EN TXT.html', $fileSearchArray);

        $lex_2024_385 = [];
        $hashResults  = [];
        foreach ($positions as $position) {
            if ($position['distance'] > 500) {
                continue;
            }

            $sanction = \trim(\strip_tags(
                SearchUtils::getTextExtract(
                    __DIR__ . '/../Data/Sanctions/EU/CELEX 32011R0753 EN TXT.html',
                    $position['start'],
                    '<tr', '</tr>'
                )
            ));

            if (\in_array($hash = \sha1($sanction), $hashResults)) {
                continue;
            }

            $hashResults[]  = $hash;
            $lex_2024_385[] = [
                'sanction_db' => 'EU_2024/385',
                'Ent_num'     => 'EU_2024/385',
                'parsed'      => $sanction,
            ];
        }

        $view->data['sanctions'] = \array_merge($view->data['sanctions'], $lex_2024_385);

        return $view;
    }

    /**
     * Create HS code view
     *
     * @param RequestAbstract  $request  Request
     * @param ResponseAbstract $response Response
     * @param array            $data     Generic data
     *
     * @return RenderableInterface
     *
     * @api
     *
     * @since 1.0.0
     */
    public function viewHSCodeView(RequestAbstract $request, ResponseAbstract $response, array $data = []) : RenderableInterface
    {
        $view = new View($this->app->l11nManager, $request, $response);
        $view->setTemplate('/Modules/Customs/Theme/Backend/hscode-view');

        $original = $request->getDataString('id') ?? '';

        if (!$request->hasData('id')
            || \preg_match('/^[0-9 ]+$/', $original) !== 1
        ) {
            return $view;
        }

        $con = new \phpOMS\DataStorage\Database\Connection\SQLiteConnection([
            'db'       => 'sqlite',
            'database' => __DIR__ . '/../Data/customs.sqlite',
        ]);

        $con->connect();

        $length = \strlen($original);

        // Also the footnotes of goods_code XX, XXXX, XXXXXX apply besides the actual code
        $codes = [
            $original,
            \substr($original, 0, 2) . \str_repeat('0', $length - 2),
            \substr($original, 0, 4) . \str_repeat('0', $length - 4),
            \substr($original, 0, 6) . \str_repeat('0', $length - 6),
        ];
        $codeString = '"' . \implode('","', $codes) . '"';

        $taricString = '';
        foreach ($codes as $code) {
            $taricString .= ' OR taric_good.Goods_code LIKE "' . $code . '%"';
        }

        $taricString = \trim($taricString, 'OR ');

        $sql = <<<SQL
        SELECT *
        FROM taric_good
        WHERE ({$taricString})
            AND taric_good.Language = "EN"
        ORDER BY taric_good.Goods_code ASC;
        SQL;

        $query = new Builder($con);

        $view->data['goods'] = $query->raw($sql)->execute()?->fetchAll() ?? [];

        if (empty($view->data['goods'])) {
            return $view;
        }

        $sql = <<<SQL
        SELECT taric_measure_footnote.*,
            taric_add_code.Description as Add_Description,
            taric_footnote_description.Description as Footnote_Description,
            taric_duties_export.Duty as Export_Duty,
            taric_duties_import.Duty as Import_Duty
        FROM taric_measure_footnote
        LEFT JOIN taric_add_code ON taric_measure_footnote.Add_code = taric_add_code.Add_code
            AND taric_add_code.Language = 'EN'
        LEFT JOIN taric_footnote_description ON taric_measure_footnote.Footnote = taric_footnote_description.Footnote
            AND taric_footnote_description.Language = 'EN'
        LEFT JOIN taric_duties_export ON taric_measure_footnote.Goods_code = taric_duties_export.Goods_code
            AND taric_duties_export.Dest_code = taric_measure_footnote.Origin_code
            AND taric_duties_export.Meas_type_code = taric_measure_footnote.Meas_type_code
        LEFT JOIN taric_duties_import ON taric_measure_footnote.Goods_code = taric_duties_import.Goods_code
            AND taric_duties_import.Origin_code = taric_measure_footnote.Origin_code
            AND taric_duties_import.Meas_type_code = taric_measure_footnote.Meas_type_code
        WHERE taric_measure_footnote.Goods_code IN ({$codeString})
        ORDER BY taric_measure_footnote.Origin_code DESC
        LIMIT 1000;
        SQL;

        $query = new Builder($con);

        $view->data['footnotes'] = $query->raw($sql)->execute()?->fetchAll() ?? [];

        return $view;
    }

    /**
     * Backend method to handle basic search request
     *
     * @param RequestAbstract  $request  Request
     * @param ResponseAbstract $response Response
     * @param array            $data     Generic data
     *
     * @return RenderableInterface
     *
     * @api
     *
     * @since 1.0.0
     */
    public function viewHSCodeDashboard(RequestAbstract $request, ResponseAbstract $response, array $data = []) : RenderableInterface
    {
        $view = new View($this->app->l11nManager, $request, $response);
        $view->setTemplate('/Modules/Customs/Theme/Backend/hscode-dashboard');

        if (!$request->hasData('hscode')) {
            return $view;
        }

        $con = new \phpOMS\DataStorage\Database\Connection\SQLiteConnection([
            'db'       => 'sqlite',
            'database' => __DIR__ . '/../Data/customs.sqlite',
        ]);

        $con->connect();

        // Search codes that contain the keyword
        $sql = <<<SQL
        SELECT taric_good.Goods_code
        FROM taric_good
        WHERE taric_good.Language = "EN"
        AND (taric_good.Description LIKE :description
            OR taric_good.Goods_code LIKE :code)
        ORDER BY taric_good.Goods_code ASC
        LIMIT 100;
        SQL;

        $query = new Builder($con);
        $query->bind(['value' => '%' . $request->getDataString('hscode') . '%'], ':description');
        $query->bind(['value' => '%' . $request->getDataString('hscode') . '%'], ':code');

        $temp = $query->raw($sql)->execute()?->fetchAll() ?? [];

        if (($exactCount = \count($temp)) === 0) {
            return $view;
        }

        // Sometimes sub-categories exist that don't contain the keyword but may be better
        // For this reason we have to also load all the sub-categories
        // Create code range by finding the last none-0 value and increasing that value by 1
        // While that value is a 9 the value is set to 0 and the left number is increased by 1
        $codeRanges   = [];
        $exactMatches = [];

        foreach ($temp as $code) {
            $exactMatches[] = $code['Goods_code'];

            if ($exactCount > 50) {
                continue;
            }

            $length = \strlen($code['Goods_code']);
            $max    = \max(
                \strrpos($code['Goods_code'], '1', -3),
                \strrpos($code['Goods_code'], '2', -3),
                \strrpos($code['Goods_code'], '3', -3),
                \strrpos($code['Goods_code'], '4', -3),
                \strrpos($code['Goods_code'], '5', -3),
                \strrpos($code['Goods_code'], '6', -3),
                \strrpos($code['Goods_code'], '7', -3),
                \strrpos($code['Goods_code'], '8', -3),
                \strrpos($code['Goods_code'], '9', -3),
            );
            $maxCode              = $code['Goods_code'];
            $maxCode[$length - 1] = '0';
            $maxCode[$length - 2] = '0';

            while ($maxCode[$max] === '9') {
                $maxCode[$max] = '0';
                --$max;
            }

            $maxCode[$max] = ((int) $code['Goods_code'][$max]) + 1;
            $codeRanges[]  = '(taric_good.Goods_code >= "' . $code['Goods_code'] . '"'
                . ' AND taric_good.Goods_code < "' . $maxCode . '")';
        }

        $codeRanges = empty($codeRanges)
            ? ''
            : ' OR (' . \implode(' OR ', $codeRanges) . ')';

        $exactMatches = '"' . \implode('","', $exactMatches) . '"';

        $sql = <<<SQL
        SELECT taric_good.Goods_code, taric_good.Indent, taric_good.Description
        FROM taric_good
        WHERE taric_good.Language = "EN"
        AND (taric_good.Goods_code IN ({$exactMatches}) {$codeRanges})
        ORDER BY taric_good.Goods_code ASC
        LIMIT 1000;
        SQL;

        $query               = new Builder($con);
        $view->data['codes'] = $query->raw($sql)->execute()?->fetchAll() ?? [];

        $con->close();

        return $view;
    }
}
