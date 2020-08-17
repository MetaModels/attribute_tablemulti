<?php

/**
 * This file is part of MetaModels/attribute_tablemulti.
 *
 * (c) 2012-2020 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels/attribute_tablemulti
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2012-2020 The MetaModels team.
 * @license    https://github.com/MetaModels/attribute_tablemulti/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

declare(strict_types=1);

namespace MetaModels\AttributeTableMultiBundle\Migration;

use Contao\CoreBundle\Migration\AbstractMigration;
use Contao\CoreBundle\Migration\MigrationResult;
use Doctrine\DBAL\Connection;

/**
 * Change the database table name from "tl_metamodel_multi" to "tl_metamodel_tablemulti".
 */
class ChangeTableNameMigration extends AbstractMigration
{
    /**
     * The database connection.
     *
     * @var Connection
     */
    private $connection;

    /**
     * Create a new instance.
     *
     * @param Connection $connection The database connection.
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * Return the name.
     *
     * @return string
     */
    public function getName(): string
    {
        return 'Change table name in MetaModels attribute tablemulti.';
    }

    /**
     * Must only run if:
     * - the MM tables tl_metamodel_multi exist.
     *
     * @return bool
     */
    public function shouldRun(): bool
    {
        $schemaManager = $this->connection->getSchemaManager();
        if ($schemaManager->tablesExist(['tl_metamodel_multi'])) {
            return true;
        }

        return false;
    }

    /**
     * Change table name tl_metamodel_multi.
     *
     * @return MigrationResult
     */
    public function run(): MigrationResult
    {
        $schemaManager = $this->connection->getSchemaManager();

        if ($schemaManager->tablesExist(['tl_metamodel_multi'])) {
            $schemaManager->renameTable('tl_metamodel_multi', 'tl_metamodel_tablemulti');

            $this->connection->createQueryBuilder()
                ->update('tl_metamodel_attribute', 't')
                ->set('t.type', ':new_name')
                ->where('t.type=:old_name')
                ->setParameter('new_name', 'tablemulti')
                ->setParameter('old_name', 'multi')
                ->execute();

            $this->connection->createQueryBuilder()
                ->update('tl_metamodel_rendersetting', 't')
                ->set('t.template', ':new_name')
                ->where('t.template=:old_name')
                ->setParameter('new_name', 'mm_attr_tablemulti')
                ->setParameter('old_name', 'mm_attr_multi')
                ->execute();

            return new MigrationResult(true, 'Rename table tl_metamodel_multi to tl_metamodel_tablemulti.');
        }

        return new MigrationResult(false, '');
    }
}
