<?php

namespace App\Tests\Domain\Schools;

use App\Domain\Schools\School;

class SchoolTest
{
    public function testSchoolCreation(): void
    {
        // Create a school instance
        $school = new School('1', 'Test School', '123 Test St');
        
        // Simple assertions
        if ($school->getId() !== '1') {
            throw new \Exception('School ID mismatch');
        }
        
        if ($school->getName() !== 'Test School') {
            throw new \Exception('School name mismatch');
        }
        
        if ($school->getAddress() !== '123 Test St') {
            throw new \Exception('School address mismatch');
        }
    }
}