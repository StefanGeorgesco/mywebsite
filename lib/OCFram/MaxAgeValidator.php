<?php
namespace OCFram;

class MaxAgeValidator extends Validator
{
    protected $maxAge;

    public function __construct($errorMessage, $maxAge)
    {
        parent::__construct($errorMessage);

        $this->setMaxAge($maxAge);
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

        return $age <= $this->maxAge;
    }

    public function ScriptValidationExpression()
    {
        $maxAge = $this->maxAge;

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

            return age <= $maxAge;
        }(elem.value)";
    }

    public function setMaxAge($maxAge)
    {
        $maxAge = (int) $maxAge;

        if ($maxAge > 0)
        {
            $this->maxAge = $maxAge;
        }
        else
        {
            throw new \RuntimeException(
                'L\'age minimal doit être un nombre supérieur à 0'
            );
        }
    }
}
