<?php

namespace CanadasMotorcycle;

/**
 * Class Model
 *
 * Abstract away the calls to PDO. In addition, create the table in the DB and
 * populate it with dummy data upon first run.
 *
 * @package CanadasMotorcycle
 */
class Model
{
    // Properties //

    /**
     * @var string Name of database.
     */
    private $dbName;

    /**
     * @var string $dbUser The DB user.
     */
    private $dbUser;

    /**
     * @var string $dbPassword The DB password.
     */
    private $dbPassword;

    /**
     * @var PDO $connection The connection to the database.
     */
    private $dbConnection;

    private $tableNameProducts;
    private $tableNameCart;
    private $tableNameApp;


    // Methods //


    public function __construct()
    {
        // Setup the app's Model (database related).
        $this->setup();
    }

    /**
     * Filter out all non-Latin-alphabet characters.
     *
     * @param string $string String to filter.
     *
     * @return string String with only Latin alphabet characters.
     */
    private function alphabetFilter($string)
    {
        if (strlen($string)) {
            return preg_replace('/[^a-z]/i', '', $string);
        }
    }

    /**
     * Split a string on capital letters.
     *
     * This will effectively render a camelCase into ['camel', 'Case'].
     *
     * @param $string
     * @return array
     */
    private function capitalStringSplit($string)
    {
        if (strlen($string)) {
            // Also, filter out empties.
            return array_filter(preg_split('/(?=[A-Z])/', $string));
        }
    }

    /**
     * Create a connection to the database.
     */
    private function connect()
    {
        try {
            $db = new \PDO(
                "mysql:host=localhost;dbname=$this->dbName;charset=utf8",
                $this->dbUser,
                $this->dbPassword,
                array(\PDO::ATTR_PERSISTENT => true)
            );
            $db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        } catch (\PDOException $ex) {
            // Handling this exception is beyond the scope of this test.
            $db = null;
        }

        // Save connection, or kill the app, because DB probably does not exist.
        if (!empty($db)) {
            $this->dbConnection = $db;
        } else {
            // No need to be overly graceful.
            trigger_error("You need to create the '$this->dbName' database first.", E_USER_ERROR);
        }
    }

    /**
     * Create the relevant tables for this app. This only called once.
     */
    private function createTables()
    {
        $tablesCreated = false;
        $tableCreatedTmpFile = '../setup.tables-created';

        // If tables are already created, skip this.
        if (!file_exists($tableCreatedTmpFile)) {

            // Create products table.
            try {
                $sql = "CREATE TABLE $this->tableNameProducts(
                    product_id INT(11) AUTO_INCREMENT PRIMARY KEY,
                    name VARCHAR(100) NOT NULL,
                    description VARCHAR(250) NULL,
                    price DECIMAL(6,2) NULL,
                    image_url VARCHAR(250) NOT NULL
                );";

                $result_products = $this->dbConnection->exec($sql);
            } catch (\PDOException $ex) {
                print_r($ex);
            }

            // Create cart table.
            try {
                $sql = "CREATE TABLE $this->tableNameCart(
                    cart_id INT(11) AUTO_INCREMENT PRIMARY KEY,
                    user_id INT(11) NOT NULL DEFAULT 1,
                    product_id INT(11) NOT NULL,
                    quantity INT(11) NOT NULL DEFAULT 0,
                    KEY user_id (user_id),
                    KEY product_id (product_id)
                );";

                $result_cart = $this->dbConnection->exec($sql);
            } catch (\PDOException $ex) {
                print_r($ex);
            }

            // Tables created successfully. Return bool true and log this.
            if (isset($result_products) && isset($result_cart)) {
                $tablesCreated = true;
                // Generate setup file to prevent this from happening again.
                file_put_contents($tableCreatedTmpFile, '');
            }
        } else {
            $tablesCreated = true;
        }

        return $tablesCreated;
    }

    /**
     * Generate acceptably formatted table name.
     *
     * Resulting table name will be all lowercase, with distinct words joined
     * by underscores. Example:
     *
     *      `canadas_motorcycle_products`
     */
    private function getFullTableName($tableName)
    {
        $fullTableName = '';

        if ($tableName) {
            // Generate and format a table prefix.
            $tablePrefix = __NAMESPACE__; // CanadasMotorcycle
            $tablePrefix = $this->alphabetFilter($tablePrefix);
            $tablePrefixSplit = $this->capitalStringSplit($tablePrefix);

            // Format table name, similarly to the prefix.
            $tableName = $this->alphabetFilter($tableName);
            $tableNameSplit = $this->capitalStringSplit($tableName);

            // Merge prefix and table arrays together.
            $tableNameWordsArray = array_merge($tablePrefixSplit, $tableNameSplit);

            // Join together with underscores, and lowercase it.
            $fullTableName = strtolower(implode('_', $tableNameWordsArray));
        }

        return $fullTableName;
    }

    /**
     * Populate these tables with test data. This is only called once.
     */
    private function populateTables() {

        $tablesPopulated = false;
        $tablesPopulatedTmpFile = '../setup.tables-populated';

        // If tables are already populated, do not run again.
        if (!file_exists($tablesPopulatedTmpFile)) {

            $products = array(
                array(
                    ':name' => 'Shoei RF-120000000',
                    ':description' => 'Light Weight Multi-Ply Matrix AIM+ Shell Construction',
                    ':price' => 512.99,
                    ':image_url' => 'http://www.canadasmotorcycle.ca/media/catalog/product/cache/1/image/330x/9df78eab33525d08d6e5fb8d27136e95/0070/3837/shoei_rf1200_helmet_solid_black_rollover.jpg'
                ),
                array(
                    ':name' => 'Alpinestars S-MX 5 Boots',
                    ':description' => 'Durable, high-tech micro-fiber upper construction offering a high level of flexibility.',
                    ':price' => 249.99,
                    ':image_url' => 'http://www.canadasmotorcycle.ca/media/catalog/product/cache/1/image/9df78eab33525d08d6e5fb8d27136e95/i/m/image_861.jpg'
                ),
                array(
                    ':name' => 'Alpinestars Bionic Air Back Protector Insert',
                    ':description' => 'Alpinestars Bionic Air Back Protector Insert',
                    ':price' => 89.96,
                    ':image_url' => 'http://www.canadasmotorcycle.ca/media/catalog/product/cache/1/small_image/330x/9df78eab33525d08d6e5fb8d27136e95/2/0/2011-Alpinestars-Bionic-Air-Back-Protector-Insert-Black.jpg'
                ),
                array(
                    ':name' => 'Alpinestars GP Plus R Leather Jacket',
                    ':description' => 'A superbly styled sport riding garment featuring a multi-panel chassis with premium leather and extensive stretch paneling.',
                    ':price' => 449.96,
                    ':image_url' => 'http://www.canadasmotorcycle.ca/media/catalog/product/cache/1/image/330x/9df78eab33525d08d6e5fb8d27136e95/0079/2470/alpinestars_gp_plus_r_leather_jacket_rollover.jpg'
                )
            );

            $productsInserted = false;
            try {
                $stmt = $this->dbConnection->prepare("
                    INSERT INTO
                        $this->tableNameProducts(name, description, price, image_url)
                        VALUES(:name, :description, :price, :image_url)
                ");

                $affectedRows = 0;
                foreach($products as $product) {
                    $stmt->execute($product);
                    $affectedRows += $stmt->rowCount();
                }

                if (count($products) == $affectedRows) {
                    $productsInserted = true;
                }

            } catch(\PDOException $ex) {
                print_r($ex);
            }

            // Make sure products were all inserted correctly before inserting data
            // in the cart.
            if ($productsInserted) {
                $stmt = $this->dbConnection->query("
                    SELECT * FROM $this->tableNameProducts
                ");
                $dataProducts = $stmt->fetchAll(\PDO::FETCH_ASSOC);

                $cartInserted = false;
                $cartProductsToInsert = 3;
                $cartProductsInserted = 0;
                // Only take the first three products and insert them into the cart.
                foreach ($dataProducts as $product) {
                    if ($product['product_id'] <= $cartProductsToInsert) {
                        try {
                            $stmt = $this->dbConnection->prepare("
                                INSERT INTO
                                    $this->tableNameCart(product_id, quantity)
                                    VALUES(:product_id, :quantity)
                            ");

                            $stmt->execute(array(
                                ':product_id' => $product['product_id'],
                                ':quantity' => mt_rand(0, 5)
                            ));

                            if ($stmt->rowCount()) {
                                $cartProductsInserted++;
                            }
                        } catch (\PDOException $ex) {}
                    } else {
                        break;
                    }
                }

                if ($cartProductsInserted == $cartProductsToInsert) {
                    $cartInserted = true;
                }
            }

            // Finally, if both are successful, create a tmp file so this step
            // is not repeated anymore.
            if (!empty($cartInserted)) {
                $tablesPopulated = true;
                file_put_contents($tablesPopulatedTmpFile, '');
            }
        } else {
            $tablesPopulated = true;
        }

        return $tablesPopulated;
    }

    /**
     * Set up the app with database credentials and connection. Also, create
     * tables and test data.
     */
    private function setup()
    {
        // Save DB credentials.
        $this->dbName = DB_NAME;
        $this->dbUser = DB_USER;
        $this->dbPassword = DB_PASSWORD;

        // Store table names.
        $this->tableNameProducts = $this->getFullTableName('products');
        $this->tableNameCart = $this->getFullTableName('cart');
        $this->tableNameApp = $this->getFullTableName('app');

        // Open DB connection.
        $this->connect();

        $tablesCreated = $this->createTables();

        if ($tablesCreated) {
            $tablesPopulated = $this->populateTables();
        }
    }

    /**
     * Update the quantity of a product in the cart.
     *
     * @param $productID
     * @param $quantity
     */
    private function updateProductQuantityInCart($productID, $quantity)
    {

    }

    /**
     * Return all the data in a cart for a user.
     */
    public function getCartData($userID)
    {
        $stmt = $this->dbConnection->query("
            SELECT
              *
            FROM
              $this->tableNameCart as c
            LEFT JOIN
              $this->tableNameProducts as p
                ON c.product_id = p.product_id
            WHERE
              c.user_id = $userID
        ");

        $cartData = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        if ($cartData) {
            return $cartData;
        }
    }
}
