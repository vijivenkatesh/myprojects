<?php
/** 
 * Insert/Update Public Holidays for each state in Australia using web service
 * Enri
 *
 * @author          Viji Venkatesh
 *
 */


ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

class PublicHolidaysAPI
{
    public function ExecuteClass()
    {
        //-------------------------------------------------------------------------
        //  get state and globalconfig id
        //-------------------------------------------------------------------------
        $txtQuery = "SELECT txtState,ID FROM test_states WHERE Active='Y'";
        $objResult=$this->objManager->query($txtQuery);
        if ($objResult===false)
        {
            throw new Exception("Failed to Select States list. ");
        }
        $i=0;
        while($row = $objResult->fetch_assoc())
        {
            $key = $row['ID'];
            $value = $row['txtState'];
            $arrStateGlobalId[$i] = array($key,$value);
            $arrStateGlobal[$key] = $value;
            $i++;
        }
        //get unique state
        $arrUniqueState = array_unique($arrStateGlobal);

        //-------------------------------------------------------------------------
        //  prepare statement to insert holiday
        //-------------------------------------------------------------------------
        $txtQuery = "INSERT INTO publicholidays
                    (                  
                        txtholiday,
                        datStart,
                        datEnd,                 
                        datCreated,
                        txtActive,
                        State                        
                    )
                    VALUES
                    (
                        ?,
                        ?,
                        ?,
                        NOW(),
                        ?,
                        ?
                       
                    )";
        $objStatement = $this->objManager->prepare($txtQuery);
        if ($objStatement === false) {

            throw new Exception('Failed to prepare a statement.  Please check the logs.');
        }
        if (!$objStatement->bind_param(
            'sssssi',
            $holidatDes,
            $holidayDate,
            $holidayDate,
            $active,
            $state,
            $globalconfig
        )) {

            throw new Exception("Failed to bind parameters to statement appending public holiday.  Please check the logs.");
        }
        //-------------------------------------------------------------------------
        //  prepare statement to update holiday
        //-------------------------------------------------------------------------
        $txtQuery = "UPDATE publicholidays SET
                                datStart=?,
                                datEnd=?,                 
                                datModified=NOW()
                            WHERE  txtName= ? AND datStart<> ? AND State=?";
        $objStatementUpdate = $this->objManager->prepare($txtQuery);
        if ($objStatement === false) {
            throw new Exception('Failed to prepare a statement.  Please check the logs.');
        }
        if (!$objStatementUpdate->bind_param(
            'ssisss',
            $holidayDate,
            $holidayDate,
            $globalconfig,
            $holidatDes,
            $holidayDate,
            $state

        )) {

            throw new Exception("Failed to bind parameters to statement appending public holiday.  Please check the logs.");
        }

        //-------------------------------------------------------------------------
        //  Get Public hoilday from web service then insert/update table
        //-------------------------------------------------------------------------
        foreach($arrUniqueState as $uniqueState) {
            $year = date("Y");
            $region =strtolower($uniqueState);
            $json = self::getRequest('pubHoliday',$region);
            //$json = '[{"date":{"day":1,"month":1,"year":2018,"dayOfWeek":1},"name":[{"lang":"en","text":"New Years Day"}],"holidayType":"public_holiday"},{"date":{"day":26,"month":1,"year":2018,"dayOfWeek":5},"name":[{"lang":"en","text":"Australia Day"}],"holidayType":"public_holiday"},{"date":{"day":12,"month":3,"year":2018,"dayOfWeek":1},"name":[{"lang":"en","text":"Labour Day"}],"holidayType":"public_holiday"},{"date":{"day":30,"month":3,"year":2018,"dayOfWeek":5},"name":[{"lang":"en","text":"Good Friday"}],"holidayType":"public_holiday"},{"date":{"day":31,"month":3,"year":2018,"dayOfWeek":6},"name":[{"lang":"en","text":"The Saturday before Easter Sunday"}],"holidayType":"public_holiday"},{"date":{"day":1,"month":4,"year":2018,"dayOfWeek":7},"name":[{"lang":"en","text":"Easter Sunday"}],"holidayType":"public_holiday"},{"date":{"day":2,"month":4,"year":2018,"dayOfWeek":1},"name":[{"lang":"en","text":"Easter Monday"}],"holidayType":"public_holiday"},{"date":{"day":25,"month":4,"year":2018,"dayOfWeek":3},"name":[{"lang":"en","text":"Anzac Day"}],"holidayType":"public_holiday"},{"date":{"day":11,"month":6,"year":2018,"dayOfWeek":1},"name":[{"lang":"en","text":"Queens Birthday"}],"holidayType":"public_holiday"},{"date":{"day":28,"month":9,"year":2018,"dayOfWeek":5},"name":[{"lang":"en","text":"Friday before the AFL Grand Final"}],"holidayType":"public_holiday"},{"date":{"day":6,"month":11,"year":2018,"dayOfWeek":2},"name":[{"lang":"en","text":"Melbourne Cup Day"}],"holidayType":"public_holiday"},{"date":{"day":25,"month":12,"year":2018,"dayOfWeek":2},"name":[{"lang":"en","text":"Christmas Day"}],"holidayType":"public_holiday"},{"date":{"day":26,"month":12,"year":2018,"dayOfWeek":3},"name":[{"lang":"en","text":"Boxing Day"}],"holidayType":"public_holiday"}]';
            $result = json_decode($json, true);

            // store state wise all holidays in a array
            $j = 0;
            foreach ($result as $holidays) {
                $day = self::isInt($holidays['date']['day']);
                $month = self::isInt($holidays['date']['month']);
                $year = self::isInt($holidays['date']['year']);

                $holidayDate = $year . '-' . $month . '-' . $day;
                $arrHoliday[$j]['date'] = date("Y-m-d", strtotime($holidayDate));
                $arrHoliday[$j]['description'] = self::cleanResponse($holidays['name'][0]['text']);
                $j++;
            }

            // loop each global id and insert each holidays for each GlobalId
            foreach ($arrStateGlobalId as $eachGlobalId) {
                if($eachGlobalId[1] == $uniqueState) {
                    $active = 'Y';
                    $state=$uniqueState;
                    $globalconfig = $eachGlobalId[0];
                    foreach($arrHoliday as $eachHoliday) {
                        $holidayDate = $eachHoliday['date'];
                        $holidatDes = $eachHoliday['description'];

                        $txtQuery = "SELECT * FROM publicholidays where txtName='$holidatDes' andYEAR(datStart)=$year";
                        $objResult = $this->objManager->query($txtQuery);
                        if ($objResult === false) {

                            throw new Exception("Failed to Select Medical Assists list.  Please check the logs.");
                        }
                        $pubHolidayExist = $objResult->fetch_assoc();

                        // if record already exist update else insert
                        if($pubHolidayExist['txtName']!='')
                        {
                            if (!$objStatementUpdate->execute()) {

                                $objStatementUpdate->close();
                                $this->objManager->rollback();
                                $this->objManager->autocommit(true);
                                throw new Exception('Failed to execute query to insert new public holiday record.  Please check the logs.');
                            }
                        }else {
                            if (!$objStatement->execute()) {
                                $objStatement->close();
                                $this->objManager->rollback();
                                $this->objManager->autocommit(true);
                                throw new Exception('Failed to execute query to insert new public holiday record.  Please check the logs.');
                            }
                        }
                    }
                }
            }
        }

    }

    /**
     * Clean response content
     */
    public static function cleanResponse($json) {
        // search and remove comments like /* */ and //
        $search  = array('INSERT', 'DELETE', 'DROP', 'TRUNCATE', 'insert','delete','drop','truncate',"'",'"');
        $json = str_replace($search, '', $json);
        $json = preg_replace('/[^A-Za-z0-9\-]/', ' ', $json); // Removes special chars.
        $json = preg_replace("#(/\*([^*]|[\r\n]|(\*+([^*/]|[\r\n])))*\*+/)|([\s\t]//.*)|(^//.*)#", '', $json);
        return $json;
    }
    /**
     * Check is int
     */
    public static function isInt($intVal)
    {
        $intVal = is_int($intVal)?$intVal:0;
        return $intVal;
    }

    public static function getRequest($req,$region)
    {

        switch ($req) {
            case "stateCode":
                $appURL = "getSupportedCountries";
                break;
            case "pubHoliday":
                $year = date("Y");
                $fromDate = '01-01-'.$year;
                $toDate ='31-12-'.$year;
                $appURL = "getHolidaysForDateRange&fromDate=".$fromDate."&toDate=".$toDate."&country=aus&region=".$region."&holidayType=public_holiday";
                break;
        }
        $url = "https://kayaposoft.com/enrico/json/v2.0/?action=".$appURL;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, $url);
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }


}

?>