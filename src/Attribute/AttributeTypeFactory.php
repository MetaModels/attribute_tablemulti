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
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
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
use Contao\Validator;
use Doctrine\DBAL\Connection;
use MetaModels\Attribute\AbstractAttributeTypeFactory;

/**
 * Attribute type factory for table text attributes.
 */
class AttributeTypeFactory extends AbstractAttributeTypeFactory
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
     * @var StringUtil|Adapter
     */
    private Adapter|StringUtil $stringUtil;

    /**
     * The Validator.
     *
     * @var Validator|Adapter
     */
    private Adapter|Validator $validator;

    /**
     * {@inheritDoc}
     */
    public function __construct(Connection $connection, Adapter $stringUtil, Adapter $validator)
    {
        parent::__construct();

        $this->connection = $connection;
        $this->stringUtil = $stringUtil;
        $this->validator  = $validator;
        $this->typeName   = 'tablemulti';
        $this->typeIcon   = 'bundles/metamodelsattributetablemulti/tablemulti.png';
        $this->typeClass  = TableMulti::class;
    }

    /**
     * {@inheritDoc}
     */
    public function createInstance($information, $metaModel)
    {
        return new $this->typeClass($metaModel, $information, $this->connection, $this->stringUtil, $this->validator);
    }
}
