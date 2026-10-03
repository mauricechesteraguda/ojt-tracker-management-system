<?php

namespace App\Contracts;

interface AcademicProvider
{
    public function profile($srCode, $password, $correlationId = null);

    public function schoolYears($correlationId = null);

    public function semesters($correlationId = null);

    public function enrollmentRecords($schoolYear, $semester, $srCode, $correlationId = null);

    public function majors($course, $correlationId = null);

    public function courses($college, $correlationId = null);

    public function colleges($correlationId = null);

    public function campuses($correlationId = null);
}
