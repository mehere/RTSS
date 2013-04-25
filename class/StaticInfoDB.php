<?php
class StaticInfoDB
{
    public static function getAllClasses($sem, $year)
    {
        $db_con = Constant::connect_to_db("ntu");
        if(empty($db_con))
        {
            throw new DBException('Fail to query report', __FILE__, __LINE__);
        }
        
        $sem = mysql_real_escape_string(trim($sem));
        $year = mysql_real_escape_string(trim($year));
        
        $sql_class  = "select distinct class_name from ((ct_class_matching left join ct_lesson on ct_class_matching.lesson_id = ct_lesson.lesson_id) left join ct_semester_info on ct_lesson.sem_id = ct_semester_info.sem_id) where ct_semester_info.year = '$year' and ct_semester_info.sem_num = $sem;";
        $class_result = Constant::sql_execute($db_con, $sql_class);
        
        if(!empty($class_result))
        {
            return $class_result;
        }
        else
        {
            return array();
        }
    }
    
    public static function getAllSubjects($sem, $year)
    {
        $db_con = Constant::connect_to_db("ntu");
        if(empty($db_con))
        {
            throw new DBException('Fail to query report', __FILE__, __LINE__);
        }
        
        $sem = mysql_real_escape_string(trim($sem));
        $year = mysql_real_escape_string(trim($year));
        
        $sql_subj  = "select distinct subj_code from (ct_lesson left join ct_semester_info on ct_lesson.sem_id = ct_semester_info.sem_id) where ct_semester_info.year = '$year' and ct_semester_info.sem_num = $sem;";
        $subj_result = Constant::sql_execute($db_con, $sql_subj);
        
        if(!empty($subj_result))
        {
            return $subj_result;
        }
        else
        {
            return array();
        }
    }
}
?>
