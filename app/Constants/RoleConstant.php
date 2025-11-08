<?php

namespace App\Constants;

/**
 * User role IDs
 * 
 * Centralized constants for user roles to avoid hardcoded magic numbers
 * throughout the codebase
 * 
 * These values must match the database roles table:
 * - Role ID 1 = student
 * - Role ID 2 = instructor
 * - Role ID 3 = assistant
 * - Role ID 4 = manager
 * - Role ID 5 = admin
 */
class RoleConstant
{
    public const STUDENT = 1;
    public const INSTRUCTOR = 2;
    public const ASSISTANT = 3;
    public const MANAGER = 4;
    public const ADMIN = 5;
    
    /**
     * Get all role IDs
     */
    public static function all(): array
    {
        return [
            self::STUDENT,
            self::INSTRUCTOR,
            self::ASSISTANT,
            self::MANAGER,
            self::ADMIN,
        ];
    }
    
    /**
     * Get role name by ID
     */
    public static function getName(int $roleId): ?string
    {
        return match ($roleId) {
            self::STUDENT => 'Student',
            self::INSTRUCTOR => 'Instructor',
            self::ASSISTANT => 'Assistant',
            self::MANAGER => 'Manager',
            self::ADMIN => 'Admin',
            default => null,
        };
    }
}
