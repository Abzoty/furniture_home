<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

trait FiltersByRole
{
    /**
     * Apply role-based filtering to the query
     */
    protected function applyRoleFilters(Builder $query, $customerIdField = 'customer_id')
    {
        $user = auth()->user();
        
        if (!$user) {
            return $query->whereRaw('1 = 0'); // Return empty result for unauthenticated users
        }

        // Admin can see all records
        if ($user->role === 'admin') {
            return $query;
        }

        // Customer can only see their own records
        if ($user->role === 'customer') {
            return $query->where($customerIdField, $user->id);
        }

        return $query->whereRaw('1 = 0'); // Return empty result for unknown roles
    }

    /**
     * Check if user can access a specific resource
     */
    protected function canAccessResource($resource, $customerIdField = 'customer_id')
    {
        $user = auth()->user();
        
        if (!$user) {
            return false;
        }

        // Admin can access all resources
        if ($user->role === 'admin') {
            return true;
        }

        // Customer can only access their own resources
        if ($user->role === 'customer') {
            return $resource->{$customerIdField} == $user->id;
        }

        return false;
    }

    /**
     * Set customer_id for customer users
     */
    protected function setCustomerIdForRole(array &$data)
    {
        $user = auth()->user();
        
        if ($user && $user->role === 'customer') {
            $data['customer_id'] = $user->id;
        }
    }
}
