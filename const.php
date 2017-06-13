<?php

date_default_timezone_set('Europe/Paris');

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
$shi_stat_dropout = 6;

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

// table: participant
$par_stat_void = 0;
$par_stat_no = 1;
$par_stat_tentative = 2;
$par_stat_can = 3;
$par_stat_yes = 4;

$par_stat = array("Udefinert", "Nei", "Tentativt", "Kan hvis behov", "Ja");

// table: contigent
$con_stat_unknown = 0;
$con_stat_unpayed = 1;
$con_stat_press = 2;
$con_stat_payed = 3;
$con_stat_part = 4;

$con_stat = array("Undefinert", "Ikke betalt", "Purret", "Betalt", "Delvis betalt");

// table: absence
$abs_stat_undef = 0;
$abs_stat_sick = 1;
$abs_stat_busy = 2;
$abs_stat_away = 3;
$abs_stat_part = 4;
$abs_stat_in = 5;

$abs_stat = array("Udefinert", "Syk", "Opptatt", "skulk", "Delvis vekke", "Tilstede");

// table: event
$evt_importance_low = 0;
$evt_inportance_norm = 1;
$evt_importance_high = 2;

$evt_importance = array("Lav", "<b>Normal</b>", "<font color=red>Høy</font>");

$evt_status_draft = 0;
$evt_status_public = 1;

$evt_status = array("Draft", "Public");
