<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuthorizedNetwork extends Model
{
    protected $fillable = ['name', 'ip_address', 'is_active'];

    /**
     * Check if an IP address is authorized, supporting both exact matches and CIDR ranges.
     */
    public static function isAuthorized($ip)
    {
        $authorizedNetworks = self::where('is_active', true)->get();

        foreach ($authorizedNetworks as $network) {
            if (self::ipInNetwork($ip, $network->ip_address)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Internal logic to check if an IP matches a definition (exact or CIDR).
     */
    protected static function ipInNetwork($ip, $range)
    {
        if ($ip === $range) {
            return true;
        }

        if (str_contains($range, '/')) {
            try {
                list($net, $mask) = explode('/', $range);
                
                // IPv4 CIDR
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) && 
                    filter_var($net, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
                    $ip_long = ip2long($ip);
                    $net_long = ip2long($net);
                    $mask_long = ~((1 << (32 - $mask)) - 1);

                    if ($mask == 0) return true;
                    return ($ip_long & $mask_long) == ($net_long & $mask_long);
                }

                // IPv6 CIDR
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) && 
                    filter_var($net, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
                    
                    $ip_bin = self::ip2bin($ip);
                    $net_bin = self::ip2bin($net);
                    
                    return substr($ip_bin, 0, $mask) === substr($net_bin, 0, $mask);
                }
            } catch (\Exception $e) {
                return false;
            }
        }

        return false;
    }

    private static function ip2bin($ip)
    {
        $packed = inet_pton($ip);
        if ($packed === false) return "";
        
        $binary = "";
        for ($i = 0; $i < strlen($packed); $i++) {
            $binary .= str_pad(decbin(ord($packed[$i])), 8, '0', STR_PAD_LEFT);
        }
        return $binary;
    }
}
