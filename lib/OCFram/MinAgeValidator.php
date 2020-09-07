<?php
namespace OCFram;

class MinAgeValidator extends Validator
{
    protected $minAge;

    public function __construct($errorMessage, $minAge)
    {
        parent::__construct($errorMessage);

        $this->setMinAge($minAge);
    }

    public function isValid($value)
    {
        if ($value == '')
        {
            return true;
        }

        $birthDay = new \DateTime($value);
        $birthYear = $birthDay->format('Y');
    	$birthMonth = $birthDay->format('m');
    	$birthDate = $birthDay->format('d');
    	$now = new \DateTime();
    	$year = $now->format('Y');
    	$month = $now->format('m');
    	$stepTime = date_create_from_format(
            'Y-m-d',
            $year.'-'.$month.'-'.$birthDate
        );

    	if ($stepTime > $now)
        {
            $stepTime = date_create_from_format(
                'Y-m-d',
                $year.'-'.((string) ((int) $month - 1)).'-'.$birthDate
            );
        }

    	$totalMonths = ((int) $year - (int) $birthYear) * 12 +
            ((int) $stepTime->format('m') - (int) $birthMonth);

    	$age = (int) floor($totalMonths / 12);

        return $age >= $this->minAge;
    }

    public function ScriptValidationExpression()
    {
        $minAge = $this->minAge;

        return "function (value) {
            if (value == '')
            {
                return true;
            }
            let birthDay = new Date(value);
            birthYear = birthDay.getFullYear();
        	birthMonth = birthDay.getMonth();
        	birthDate = birthDay.getDate();
        	now = new Date();
        	year = now.getFullYear();
        	month = now.getMonth();
        	stepTime = new Date(year, month, birthDate);
        	if (stepTime > now) stepTime = new Date(year, month-1, birthDate);
        	totalMonths = (year - birthYear) * 12 + (stepTime.getMonth() - birthMonth);
        	let age = Math.floor(totalMonths / 12);

            return age >= $minAge;
        }(elem.value)";
    }

    public function setMinAge($minAge)
    {
        $minAge = (int) $minAge;

        if ($minAge > 0)
        {
            $this->minAge = $minAge;
        }
        else
        {
            throw new \RuntimeException(
                'L\'age minimal doit être un nombre supérieur à 0'
            );
        }
    }
}
