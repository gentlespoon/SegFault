<?php

class search {

    protected $clause = null;
    protected $storedClauses = array();

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
        if (is_array($_GET['keyword'])) {
            $this->startSubClause($_GET['keyword']['all'] === "1" ? 'and' : 'or');
            foreach ($_GET['keyword']['keywords'] as $keyword) {
                $this->keywordAddCondition($keyword);
            }
            $this->endSubClause();
        }
        else {
            $this->keywordAddCondition($_GET['keyword']);
        }
    }

    protected function tagAddCondition($tagID) {
        $this->addWhereLikeCond("tags", $tagID, ",");
    }

    protected function tagHandler() {
        if (is_array($_GET['tag'])) {
            $this->startSubClause($_GET['tag']['all'] === "1" ? 'and' : 'or');
            foreach ($_GET['tag']['ids'] as $tag) {
                $this->tagAddCondition($tag);
            }
            $this->endSubClause();
        }
        else {
            $this->tagAddCondition($_GET['tag']);
        }
    }

    protected function uidAddCondition($uid) {
        $this->addCond("member.uid = %i", $uid);
    }

    protected function uidHandler() {
        if (is_array($_GET['uid'])) {
            $this->startSubClause($_GET['uid']['all'] === "1" ? 'and' : 'or');
            foreach ($_GET['uid']['ids'] as $uid) {
                $this->uidAddCondition($uid);
            }
            $this->endSubClause();
        }
        else {
            $this->uidAddCondition($_GET['uid']);
        }
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
        if (is_array($_GET['username'])) {
            $this->startSubClause($_GET['username']['all'] === "1" ? 'and' : 'or');
            $this->usernameAddCondition($_GET['username']['usernames']);
            $this->endSubClause();
        }
        else {
            $this->usernameAddcondition(array($_GET['username']));
        }
    }

    protected function parseConditions() {
        if (array_key_exists("keyword", $_GET)) {
            $this->keywordHandler();
        }
        if (array_key_exists("tag", $_GET)) {
            $this->tagHandler();
        }
        if (array_key_exists("uid", $_GET)) {
            $this->uidHandler();
        }
        elseif (array_key_exists("username", $_GET)) {
            $this->usernameHandler();
        }
    }

    public function addSearchConditions() {
        $this->startSubClause(array_key_exists("all", $_GET) ? 'and' : 'or');
        $this->parseConditions();
        $this->endSubClause();
    }

    public static function genWhereLikeCond($fieldname, $condition, $delim, $allowConcatenate=false) {
        $searchStrings = search::makeSearchStrings($fieldname, $condition, $delim, $allowConcatenate);
        
        $where = new WhereClause('or');

        search::addItems($searchStrings, $where, '%l LIKE %ss', 1, $fieldname);
    
        return $where;
    }
};