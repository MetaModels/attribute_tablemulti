<?php

/**
 * This file is part of MetaModels/attribute_tablemulti.
 *
 * (c) 2012-2024 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels
 * @subpackage AttributeTableMulti
 * @author     Andreas Dziemba <adziemba@web.de>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Andreas Isaak <info@andreas-isaak.de>
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     David Greminger <david.greminger@1up.io>
 * @author     David Maack <david.maack@arcor.de>
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2012-2024 The MetaModels team.
 * @license    https://github.com/MetaModels/attribute_tablemulti/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\AttributeTableMultiBundle\Attribute;

use Contao\CoreBundle\Framework\Adapter;
use Contao\StringUtil;
use Contao\System;
use Contao\Validator;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Connection;
use MetaModels\Attribute\BaseComplex;
use MetaModels\IMetaModel;

use function array_keys;
use function array_map;
use function array_merge;
use function is_array;
use function is_int;
use function is_string;
use function str_replace;
use function substr;
use function time;

/**
 * This is the MetaModelAttribute class for handling table text fields.
 */
class TableMulti extends BaseComplex
{
    /**
     * Database connection.
     *
     * @var Connection
     */
    private Connection $connection;

    /**
     * The string util.
     *
     * @var Adapter<StringUtil>
     */
    private Adapter $stringUtil;

    /**
     * The validator.
     *
     * @var Adapter<Validator>
     */
    private Adapter $validator;

    /**
     * Instantiate an MetaModel attribute.
     *
     * Note that you should not use this directly but use the factory classes to instantiate attributes.
     *
     * @param IMetaModel      $objMetaModel The MetaModel instance this attribute belongs to.
     * @param array           $arrData      The information array, for attribute information, refer to documentation of
     *                                      table tl_metamodel_attribute and documentation of the certain attribute
     *                                      classes for information what values are understood.
     * @param Connection|null $connection   The database connection.
     * @param Adapter|null    $stringUtil   The string util.
     * @param Adapter|null    $validator    The validator.
     */
    public function __construct(
        IMetaModel $objMetaModel,
        array $arrData = [],
        Connection $connection = null,
        Adapter $stringUtil = null,
        Adapter $validator = null
    ) {
        parent::__construct($objMetaModel, $arrData);

        if (null === $connection) {
            // @codingStandardsIgnoreStart
            @trigger_error(
                'Connection is missing. It has to be passed in the constructor. Fallback will be dropped.',
                E_USER_DEPRECATED
            );
            // @codingStandardsIgnoreEnd
            $connection = System::getContainer()->get('database_connection');
            assert($connection instanceof Connection);
        }

        if (null === $stringUtil) {
            // @codingStandardsIgnoreStart
            @trigger_error(
                'StringUtil Adapter is missing. It has to be passed in the constructor. Fallback will be dropped.',
                E_USER_DEPRECATED
            );
            // @codingStandardsIgnoreEnd
            $stringUtil = System::getContainer()->get('contao.framework')?->getAdapter(StringUtil::class);
            assert($stringUtil instanceof Adapter);
        }

        if (null === $validator) {
            // @codingStandardsIgnoreStart
            @trigger_error(
                'Validator Adapter is missing. It has to be passed in the constructor. Fallback will be dropped.',
                E_USER_DEPRECATED
            );
            // @codingStandardsIgnoreEnd
            $validator = System::getContainer()->get('contao.framework')?->getAdapter(Validator::class);
            assert($validator instanceof Adapter);
        }

        $this->connection = $connection;
        $this->stringUtil = $stringUtil;
        $this->validator  = $validator;
    }

    /**
     * {@inheritdoc}
     */
    public function searchFor($strPattern)
    {
        $strPattern = str_replace(['*', '?'], ['%', '_'], $strPattern);

        $statement = $this->connection
            ->createQueryBuilder()
            ->select('t.item_id')
            ->from($this->getValueTable(), 't')
            ->where('t.value LIKE :pattern')
            ->andWhere('t.att_id = :id')
            ->setParameter('pattern', $strPattern)
            ->setParameter('id', $this->get('id'))
            ->executeQuery();

        // Return value list as list<mixed>, parent function wants a list<string> so we make a cast.
        return array_map(static fn (mixed $value) => (string) $value, $statement->fetchFirstColumn());
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributeSettingNames()
    {
        return array_merge(parent::getAttributeSettingNames(), []);
    }

    /**
     * Return the table we are operating on.
     *
     * @return string
     */
    protected function getValueTable()
    {
        return 'tl_metamodel_tablemulti';
    }

    /**
     * {@inheritdoc}
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    public function getFieldDefinition($arrOverrides = [])
    {
        // Get table and column
        $strTable = $this->getMetaModel()->getTableName();
        $strField = $this->getColName();

        $arrFieldDef                         = parent::getFieldDefinition($arrOverrides);
        $arrFieldDef['inputType']            = 'multiColumnWizard';
        $arrFieldDef['eval']['columnFields'] = [];

        // Check for override in local config.
        if (isset($GLOBALS['TL_CONFIG']['metamodelsattribute_multi'][$strTable][$strField])) {
            // Retrieve the config.
            $config = $GLOBALS['TL_CONFIG']['metamodelsattribute_multi'][$strTable][$strField];

            // Add CSS class.
            if (!empty($arrFieldDef['eval']['tl_class'])) {
                $config['tl_class'] = isset($config['tl_class'])
                    ? $config['tl_class'] . ' ' . $arrFieldDef['eval']['tl_class']
                    : $arrFieldDef['eval']['tl_class'];
            }

            // Hide buttons if readonly.
            if (!empty($arrFieldDef['eval']['readonly'])) {
                $config['hideButtons'] = true;
            }

            // Add field configs.
            foreach ($config['columnFields'] as $col => $data) {
                $config['columnFields']['col_' . $col] = $data;
                unset($config['columnFields'][$col]);

                // Add readonly and delete picker and wizard class.
                if (!empty($arrFieldDef['eval']['readonly'])) {
                    $config['columnFields']['col_' . $col]['eval']['readonly'] = true;
                    unset(
                        $config['columnFields']['col_' . $col]['eval']['dcaPicker'],
                        $config['columnFields']['col_' . $col]['eval']['datepicker'],
                        $config['columnFields']['col_' . $col]['eval']['colorpicker']
                    );
                    $config['columnFields']['col_' . $col]['eval']['tl_class'] =
                        str_replace('wizard', '', $config['columnFields']['col_' . $col]['eval']['tl_class'] ?? '');
                }
            }

            // Append the eval config.
            $arrFieldDef['eval'] = $config;
        }

        return $arrFieldDef;
    }

    /**
     * {@inheritdoc}
     */
    public function setDataFor($arrValues)
    {
        // Check if we have an array.
        if (empty($arrValues)) {
            return;
        }

        // Get the ids.
        $arrIds = array_keys($arrValues);

        // Reset all data for the ids.
        $this->unsetDataFor($arrIds);

        foreach ($arrIds as $intId) {
            // Walk every row.
            foreach ((array) $arrValues[$intId] as $row) {
                // Walk every column and update / insert the value.
                foreach ($row as $col) {
                    $values = $this->getSetValues($col, $intId);

                    $queryBuilder = $this->connection->createQueryBuilder()->insert($this->getValueTable());
                    foreach ($values as $name => $value) {
                        $queryBuilder
                            ->setValue($this->getValueTable() . '.' . $name, ':' . $name)
                            ->setParameter($name, $value);
                    }

                    $sql        = $queryBuilder->getSQL();
                    $parameters = $queryBuilder->getParameters();

                    $queryBuilder = $this->connection->createQueryBuilder()->update($this->getValueTable());
                    foreach ($values as $name => $value) {
                        $queryBuilder
                            ->set($this->getValueTable() . '.' . $name, ':' . $name)
                            ->setParameter($name, $value);
                    }

                    $updateSql = $queryBuilder->getSQL();
                    $sql      .= ' ON DUPLICATE KEY ' . str_replace($this->getValueTable() . ' SET ', '', $updateSql);

                    $this->connection->executeQuery($sql, $parameters);
                }
            }
        }
    }

    /**
     * {@inheritdoc}
     *
     * Fetch filter options from foreign table.
     */
    public function getFilterOptions($idList, $usedOnly, &$arrCount = null)
    {
        $builder = $this->connection->createQueryBuilder()
            ->select('t.value, COUNT(t.value) as mm_count')
            ->from($this->getValueTable(), 't')
            ->andWhere('t.att_id = :att_id')
            ->setParameter('att_id', $this->get('id'))
            ->groupBy('t.value');

        if (null !== $idList) {
            $builder
                ->andWhere('t.item_id IN (:id_list)')
                ->orderBy('FIELD(t.id,:id_list)')
                ->setParameter('id_list', $idList, ArrayParameterType::INTEGER);
        }

        $statement = $builder->executeQuery();

        $arrResult = [];
        while ($objRow = $statement->fetchAssociative()) {
            $strValue = $objRow['value'];

            if (is_array($arrCount)) {
                $arrCount[$strValue] = $objRow['mm_count'];
            }

            $arrResult[$strValue] = $strValue;
        }

        return $arrResult;
    }

    /**
     * {@inheritdoc}
     */
    public function getDataFor($arrIds)
    {
        $queryBuilder = $this->connection->createQueryBuilder()
            ->select('*')
            ->from($this->getValueTable(), 't')
            ->orderBy('t.row', 'ASC')
            ->addOrderBy('t.col', 'ASC');

        $this->buildWhere($queryBuilder, $arrIds, null, null, 't');

        $statement = $queryBuilder->executeQuery();
        $arrReturn = [];

        while ($row = $statement->fetchAssociative()) {
            // The contao deserialize will check if we have some serialized data or not.
            $row['value'] = \Contao\StringUtil::deserialize($row['value']);
            $arrReturn[$row['item_id']][$row['row']][$row['col']] = $row;
        }

        return $arrReturn;
    }

    /**
     * {@inheritdoc}
     */
    public function unsetDataFor($arrIds)
    {
        $queryBuilder = $this->connection->createQueryBuilder()->delete($this->getValueTable());
        $this->buildWhere($queryBuilder, $arrIds, null, null, $this->getValueTable());

        $queryBuilder->executeQuery();
    }

    /**
     * Build the where clause.
     *
     * @param QueryBuilder             $queryBuilder The query builder.
     * @param null|list<string>|string $mixIds       One, none or many ids to use.
     * @param int|null                 $intRow       The row number, optional.
     * @param string|null              $varCol       The col number, optional.
     * @param string                   $tableAlias   The table alias, optional.
     */
    protected function buildWhere(
        QueryBuilder $queryBuilder,
        $mixIds,
        $intRow = null,
        $varCol = null,
        string $tableAlias = ''
    ): void {
        if ('' !== $tableAlias) {
            $tableAlias .= '.';
        }

        $queryBuilder
            ->andWhere($tableAlias . 'att_id = :att_id')
            ->setParameter('att_id', (int) $this->get('id'));

        if (is_int($intRow) && is_string($varCol)) {
            $queryBuilder
                ->andWhere($tableAlias . 'row = :row AND ' . $tableAlias . 'col = :col')
                ->setParameter('row', $intRow)
                ->setParameter('col', $varCol);
        }

        if (null === $mixIds) {
            return;
        }
        if (is_array($mixIds)) {
            if ([] === $mixIds) {
                return;
            }

            $queryBuilder
                ->andWhere($tableAlias . 'item_id IN (:item_ids)')
                ->setParameter('item_ids', $mixIds, ArrayParameterType::STRING);

            return;
        }
        $queryBuilder
            ->andWhere($tableAlias . 'item_id = :item_id')
            ->setParameter('item_id', $mixIds);
    }

    /**
     * {@inheritdoc}
     */
    public function valueToWidget($varValue)
    {
        if (!is_array($varValue)) {
            return [];
        }

        $widgetValue = [];
        foreach ($varValue as $row) {
            foreach ($row as $col) {
                $widgetValue[$col['row']]['col_' . $col['col']] = $col['value'];
            }
        }

        return $widgetValue;
    }

    /**
     * {@inheritdoc}
     */
    public function widgetToValue($varValue, $itemId)
    {

        if (!is_array($varValue)) {
            return [];
        }

        $newValue = [];
        // Start row numerator at 0.
        $intRow = 0;
        foreach ($varValue as $k => $row) {
            foreach ($row as $kk => $col) {
                $kk = substr($kk, 4);

                $newValue[$k][$kk]['value'] = $col;
                $newValue[$k][$kk]['col']   = $kk;
                $newValue[$k][$kk]['row']   = $intRow;
            }
            $intRow++;
        }

        return $newValue;
    }

    /**
     * Calculate the array of query parameters for the given cell.
     *
     * @param array  $arrCell The cell to calculate.
     * @param string $intId   The data set id.
     *
     * @return array
     */
    protected function getSetValues($arrCell, $intId)
    {
        $value = $arrCell['value'];
        // Convert the value, if is a binary uuid to a string uuid, for save in text blob column.
        if (
            $this->validator->isBinaryUuid($value)
            && $this->validator->isStringUuid($convertedValue = $this->stringUtil->binToUuid($value))
        ) {
            $value = $convertedValue;
        } elseif (\is_array($value)) {
            $value = \serialize($value);
        }

        return [
            'tstamp'  => time(),
            'value'   => (string) $value,
            'att_id'  => $this->get('id'),
            'row'     => (int) $arrCell['row'],
            'col'     => $arrCell['col'],
            'item_id' => $intId,
        ];
    }
}
