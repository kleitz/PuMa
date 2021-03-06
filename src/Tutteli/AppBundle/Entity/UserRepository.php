<?php
/*
 * This file is part of the project tutteli/puma published under the Apache License 2.0
 * For the full copyright and license information, please have a look at LICENSE in the
 * root folder or visit https://github.com/robstoll/PuMa
 */

namespace Tutteli\AppBundle\Entity;

class UserRepository extends ARepository
{
    protected function getEntityIdentifier() {
        return 'TutteliAppBundle:User';
    }
}
