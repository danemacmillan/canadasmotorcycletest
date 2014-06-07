<?php

namespace CanadasMotorcycle;

/**
 * Class Model
 *
 * Abstract away the calls to PDO. In addition, create the table in the DB and
 * populate it with dummy data upon first run.
 *
 * @author Dane MacMillan <work@danemacmillan.com>
 *
 * @package CanadasMotorcycle
 */
class Model
{
    // Constants //


    /**
     * General sales tax percentage.
     */
    const GST = 5;

    /**
     * Quebec sales tax percentage.
     */
    const QST = 9.975;


    // Properties //


    /**
     * @var PDO $connection The connection to the database.
     */
    private $dbConnection;

    /**
     * @var string Name of database.
     */
    private $dbName;

    /**
     * @var string $dbPassword The DB password.
     */
    private $dbPassword;

    /**
     * @var string $dbUser The DB user.
     */
    private $dbUser;

    /**
     * @var string #tableNameCart The name of the cart table.
     */
    private $tableNameCart;

    /**
     * @var string $tableNameProducts The name of the products table.
     */
    private $tableNameProducts;


    // Methods //


    /**
     * Setup the app's model.
     */
    public function __construct()
    {
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
     * Calculate an arbitrary amount of tax against a number.
     *
     * @param float $subTotal The subtotal.
     * @param float $taxPercentage The tax percentage.
     *
     * @return float
     */
    private function calculateTax($subTotal, $taxPercentage)
    {
        return ($subTotal > 0)
            ? $subTotal * ($taxPercentage / 100)
            : 0.00;
    }

    /**
     * Split a string on capital letters.
     *
     * This will effectively render a camelCase into ['camel', 'Case'].
     *
     * @param string $string A string to be split.
     *
     * @return array Individual words.
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
     * Create the relevant tables for this app. This is only called once.
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
                trigger_error('Products table could be not created', E_USER_NOTICE);
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
                trigger_error('Cart table could not be created', E_USER_NOTICE);
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
     * Return all the data in a cart for a user.
     */
    public function getCartData($userID)
    {
        $cartData = array();

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

        $dataDump = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        if ($dataDump) {
            $cartData = $dataDump;
        }

        return $cartData;
    }

    /**
     * Calculate the final price of all items in cart, with taxes and total.
     *
     * @param int $subTotal The subtotal of all purchases.
     *
     * @return array Parsed for view.
     */
    public function getFinalPrices($subTotal)
    {
        $cartGst = $this->calculateTax($subTotal, self::GST);
        $cartQst = $this->calculateTax($subTotal, self::QST);
        $cartTotal = $subTotal + $cartGst + $cartQst;

        return array(
            'cart_subtotal' => number_format($subTotal, 2, '.', ' '),
            'cart_gst' => number_format($cartGst, 2, '.', ' '),
            'cart_qst' => number_format($cartQst, 2, '.', ' '),
            'cart_total' => number_format($cartTotal, 2, '.', ' ')
        );
    }

    /**
     * Calculate the cart subtotal.
     *
     * @param array $cartData The cart data.
     *
     * @return float
     */
    public function getCartSubtotal($cartData)
    {
        $cartSubTotal = 0;
        foreach ($cartData as $cartItem) {
            $cartSubTotal += $cartItem['price'] * $cartItem['quantity'];
        }

        return ($cartSubTotal)
            ? $cartSubTotal
            : 0.00;
    }

    /**
     * Calculate the cart quantity.
     *
     * @param array $cartData The cart data.
     * @return int
     */
    public function getCartQuantity($cartData)
    {
        $cartQuantity = 0;
        foreach ($cartData as $cartItem) {
            $cartQuantity += $cartItem['quantity'];
        }

        return ($cartQuantity)
            ? $cartQuantity
            : 0;
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
    private function populateTables()
    {
        $tablesPopulated = false;
        $tablesPopulatedTmpFile = '../setup.tables-populated';

        // If tables are already populated, do not run again.
        if (!file_exists($tablesPopulatedTmpFile)) {

            // Static test data. This is typically dynamic content, but for the
            // purposes of this test, DATA IS NEEDED!
            $products = array(
                array(
                    ':name' => 'Shoei RF-1200',
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
                trigger_error('Products could not be inserted.', E_USER_NOTICE);
            }

            // Make sure products were all inserted correctly before inserting data
            // in the cart.
            if ($productsInserted) {
                try {
                    $stmt = $this->dbConnection->query("
                        SELECT * FROM $this->tableNameProducts
                    ");
                    $dataProducts = $stmt->fetchAll(\PDO::FETCH_ASSOC);
                } catch (\PDOException $ex) {
                    trigger_error('Products table could not be queried', E_USER_NOTICE);
                }

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
                                'product_id' => $product['product_id'],
                                'quantity' => mt_rand(1, 5)
                            ));

                            if ($stmt->rowCount()) {
                                $cartProductsInserted++;
                            }
                        } catch (\PDOException $ex) {
                            trigger_error('Cart data could not be populated.', E_USER_NOTICE);
                        }
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

        // Create and populate the tables. Once complete, the project will
        // output its own provision files, so these steps are not run again.
        $tablesCreated = $this->createTables();
        if ($tablesCreated) {
            $tablesPopulated = $this->populateTables();
        }

        if (!$tablesCreated || !$tablesPopulated) {
            trigger_error("The app's database tables could not be setup", E_USER_NOTICE);
        }
    }

    /**
     * Update the quantity of a product in the cart.
     *
     * @param array $postData Data straight from form POST.
     *
     * @return bool True if updated, false on failure.
     */
    public function updateProductQuantityInCart($postData)
    {
        // Add static userID to array. (Static for test.)
        $postData['user_id'] = App::$userID;

        $updated = false;
        try {
            $stmt = $this->dbConnection->prepare("
                UPDATE
                    $this->tableNameCart
                SET
                  quantity = :quantity
                WHERE
                  cart_id = :cart_id
                  AND  user_id = :user_id
                  AND product_id = :product_id
            ");

            $updated = $stmt->execute($postData);
        } catch (\PDOException $ex) {
            trigger_error('Cart quantity could not be updated.', E_USER_NOTICE);
        }

        return $updated;
    }
}
