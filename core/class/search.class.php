<?php

class search {

    protected $clause = null;
    protected $storedClauses = array();

    protected $all = array();
    protected $keywords = array();
    protected $usernames = array();
    protected $uids = array();
    protected $tagIDs = array();

    function __construct(string $boolOp = "and") {
        $this->clause = new WhereClause($boolOp);
        $this->storedClauses = array();
    }

    /**
     * @param none
     * @return WhereClause if all subclauses have been ended
     * @return null if a subclause has not been ended
     */
    function getWhereCond() {
        if (empty($this->storedClauses)) {
            return $this->clause;
        }
        else {
            return null;
        }
    }

    public function addCond(string $cond, ...$vals) {
        $this->clause->add($cond, ...$vals);
    }

    public function startSubClause(string $boolOp) {
        array_push($this->storedClauses, $this->clause);
        $this->clause = $this->clause->addClause($boolOp);
    }

    public function endSubClause() {
        $this->clause = array_pop($this->storedClauses);
    }

    public function negateLast() {
        $this->clause->negatelast();
    }

    public function negate() {
        $this->clause->negate();
    }

    protected static function makeSearchStrings($fieldname, $condition, $delim, $allowConcatenate) {
        $searchStrings = array($condition.$delim.'%', 
                               '%'.$delim.$condition.$delim.'%', 
                               '%'.$delim.$condition, 
                               $condition);
      
        if ($allowConcatenate) {
            array_push($searchStrings, '%'.$condition.'%');
        }
      
        return $searchStrings;
    }

    /**
     * @param array of items to add to clause
     * @param WhereClause to add to
     * @param string containing condition
     * @param int where in the argument list the 'item' from 'items' should be inserted at
     * @param array all other arguments to be passed to WhereClause::add
     * @return none
     */
    protected static function addItems($items, $clause, $cond, $offset, ...$params) {
        foreach ($items as $item) {
            array_splice($params, $offset, 0, $item); //insert this item into params at offset
            $clause->add($cond, ...$params);
            array_splice($params, $offset, 1); //remove this item from params
        }
    }

    public function addWhereLikeCond($fieldname, $condition, $delim, $allowConcatenate=false) {
        $searchStrings = $this->makeSearchStrings($fieldname, $condition, $delim, $allowConcatenate);
      
        $subClause = $this->clause->addClause('or');

        $this->addItems($searchStrings, $subClause, '%l LIKE %ss', 1, $fieldname);
    }
      
    public function makeWhereLikeCond($fieldname, $condition, $delim, $allowConcatenate=false) {
        $searchStrings = $this->makeSearchStrings($fieldname, $condition, $delim, $allowConcatenate);
      
        $clause = new WhereClause('or');

        $this->addItems($searchStrings, $clause, '%l LIKE %ss', 1, $fieldname);
      
        $this->clause = $clause;
        $this->storedClauses = array();
    }

    protected function keywordAddCondition($keyword) {
        $this->startSubClause('or');
        $this->addWhereLikeCond("title", $keyword, " ", true);
        $this->addWhereLikeCond("content", $keyword, " ", true);
        $this->endSubClause();
    }

    protected function keywordHandler() {
        $this->startSubClause(in_array("keywords", $this->all) ? 'and' : 'or');
        foreach ($this->keywords as $keyword) {
            $this->keywordAddCondition($keyword);
        }
        $this->endSubClause();
    }

    protected function tagAddCondition($tagID) {
        $this->addWhereLikeCond("tags", $tagID, ",");
    }

    protected function tagHandler() {
        $this->startSubClause(in_array("tags", $this->all) ? 'and' : 'or');
        foreach ($this->tagIDs as $tagID) {
            $this->tagAddCondition($tagID);
        }
        $this->endSubClause();
    }

    protected function uidAddCondition($uid) {
        $this->addCond("member.uid = %i", $uid);
    }

    protected function uidHandler() {
        $this->startSubClause(in_array("uids", $this->all) ? 'and' : 'or');
        foreach ($this->uids as $uid) {
            $this->uidAddCondition($uid);
        }
        $this->endSubClause();
    }

    protected function getUIDs($usernames) {
        $where = new WhereClause('or');

        foreach ($usernames as $username) {
            $where->add("username = %s", $username);
        }

        return array_column(DB::query("SELECT uid FROM member WHERE %l", $where), 'uid');
    }

    protected function usernameAddCondition($usernames) {
        $uids = $this->getUIDs($usernames);

        if (empty($uids)) { //handle the case of no users by that name
            $this->uidAddCondition(0); //uid of guests, guests can't post -> no results
        }
        else {
            foreach ($uids as $uid) {
                $this->uidAddCondition($uid);
            }
        }
    }

    protected function usernameHandler() {
        $this->startSubClause(in_array("usernames", $this->all) ? 'and' : 'or');
        $this->usernameAddCondition($this->usernames);
        $this->endSubClause();
    }

    protected function parseConditions() {
        if (!empty($this->keywords)) {
            $this->keywordHandler();
        }
        if (!empty($this->tagIDs)) {
            $this->tagHandler();
        }
        if (!empty($this->uids)) {
            $this->uidHandler();
        }
        elseif (!empty($this->usernames)) {
            $this->usernameHandler();
        }
    }

    protected function fetchFromGet() {
        if (array_key_exists("keyword", $_GET)) {
            array_push($this->keywords, $_GET['keyword']);
        }
        if (array_key_exists("tag", $_GET)) {
            array_push($this->tagIDs, $_GET['tag']);
        }
        if (array_key_exists("uid", $_GET)) {
            array_push($this->uids, $_GET['uid']);
        }
        elseif (array_key_exists("username", $_GET)) {
            array_push($this->usernames, $_GET['username']);
        }
    }

    protected function fetchFromPost() {
        $conditions = json_decode($_POST['search'], true);

        $this->all = $conditions['all'];
        $this->keywords = $conditions['keywords'];
        $this->usernames = $conditions['usernames'];
        $this->tagIDs = $conditions['tags'];
    }

    protected function parseSearchCondJSON($JSON) {
        $decoded = json_decode($JSON);

        $this->all = $decoded[0];
        $this->keywords = $decoded[1];
        $this->usernames = $decoded[2];
        $this->uids = $decoded[3];
        $this->tagIDs = $decoded[4];
    }

    protected function fetchConditions($JSON) {
        if ($JSON !== null) {
            $this->parseSearchCondJSON($JSON);
        }
        elseif (array_key_exists("search", $_POST)) {
            $this->fetchFromPost();
        }
        else {
            $this->fetchFromGet();
        }
    }

    public function addSearchConditions($JSON = null) {
        $this->fetchConditions($JSON);
        $this->startSubClause(in_array("all", $this->all) ? 'and' : 'or');
        $this->parseConditions();
        $this->endSubClause();
    }

    public function getSearchCondJSON() {
        return json_encode(array($this->all, $this->keywords, $this->usernames, $this->uids, $this->tagIDs));
    }

    public static function genWhereLikeCond($fieldname, $condition, $delim, $allowConcatenate=false) {
        $searchStrings = search::makeSearchStrings($fieldname, $condition, $delim, $allowConcatenate);
        
        $where = new WhereClause('or');

        search::addItems($searchStrings, $where, '%l LIKE %ss', 1, $fieldname);
    
        return $where;
    }
};