<?php 

Class Student{
    private string $name;
    private array $grades = [];

    public function __construct($name)
    {
        $this->name = $name;
    }

    public function addGrade($grade)
    {
        $this->grades[] = $grade;
    }

    public function getAverage()
    {
        $sum = 0;
        $count = count($this->grades);

        foreach($this->grades as $grade){
            $sum += $grade;
        }

        $average = $sum / $count;

        return $average;
    }
}