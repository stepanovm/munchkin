<?php


namespace Lynxx\AssetsHelpers;


class AssetsVersionResolver

    private $versions_cfg = array();

    private $res_type;

    public function __construct(\PDO $pdo, $res_type) {
        parent::__construct();
        $this->res_type = $res_type;
        $this->initResourcesCfg();
    }

    public function getResourcesCfg()
    {
        return $this->versions_cfg;
    }




    private function initResourcesCfg()
    {
        try{
            $cfg_data = DB::selectUnique('SELECT wr.url, wr.* FROM web_resources wr WHERE res_type = ?', array($this->res_type));

            $stmt = self::instance()->prepare($sqlQuery);
            $stmt->execute($args);
            return $stmt->fetchAll(PDO::FETCH_UNIQUE);

            $this->versions_cfg = $cfg_data;
        } catch (\Exception $ex) {
            $this->createResourcesTable();
        }
    }

    private function createResourcesTable()
    {
        $query = "
            CREATE TABLE IF NOT EXISTS web_resources
            (
                id int(11) NOT NULL AUTO_INCREMENT,
                v int(11),
                url varchar(255) NOT NULL UNIQUE KEY,
                res_type varchar(30) NOT NULL,
                last_modified_time TIMESTAMP NOT NULL,
                PRIMARY KEY (id)
            )
        ";
        \app\core\DB_admin::instance()->exec($query);
    }

    public function getResourceVersion($url, $res_type, $last_modified = false)
    {
        if(array_key_exists($url, $this->versions_cfg)){
            if($last_modified !== false && $this->versions_cfg[$url]['last_modified_time'] < $last_modified){
                $query = 'UPDATE web_resources SET v = v + 1, last_modified_time = ? WHERE url = ?';
                DB::update($query, array($last_modified, $url));
                $this->versions_cfg[$url]['v']++;
            }
        } else {
            $query = 'INSERT IGNORE INTO web_resources (url, v, last_modified_time, res_type) VALUES (?, ?, ?, ?)';
            DB::insert($query, array($url, 1, $last_modified, $res_type));
            $this->versions_cfg[$url]['v'] = 1;
        }
        return $this->versions_cfg[$url]['v'];
    }

}