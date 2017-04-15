<?php

// table: person

    // field: status
    $per_stat_quited = 0;
    $per_stat_member = 1;
    $per_stat_standin = 2;
    $per_stat_hired = 3;
    $per_stat_eng = 4;
    
    $per_stat = array("Sluttet", "Medlem", "Vikar", "Innleid", "Engasjert");

    // field: status_dir
    $per_dir_avail = 0;
    $per_dir_nocarry = 1;
    $per_dir_exempt = 2;
    
    $per_dir = array("Ledig", "Kan ikke bære bord", "Fritatt");
    
// table: shift
    $shi_stat_free = 0;
    $shi_stat_tentative = 1;
    $shi_stat_confirmed = 2;
    $shi_stat_failed = 3;
    $shi_stat_leave = 4;
    $shi_stat_responsible = 5;
    
// table: project
    $prj_stat_public = 0;
    $prj_stat_internal = 1;
    $prj_stat_tentative = 2;
    $prj_stat_draft = 3;
    $prj_stat_canceled = 4;
    
    $prj_stat = array("Public", "Internt", "Tentativt", "Draft", "Kanselert");
    
    $prj_orch_reduced = 0;
    $prj_orch_tutti = 1;
    
// table: direction
    $dir_stat_free = 0;
    $dir_stat_allocated = 1;
    
// table: plan
    $plan_evt_rehearsal = 0;
    $plan_evt_direction = 1;