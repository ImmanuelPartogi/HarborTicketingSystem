<?php
// Setting.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string, mixed>
     */
    protected $fillable = [
        'key',
        'value',
    ];

    /**
     * Cache prefix for settings
     */
    const CACHE_PREFIX = 'setting_';

    /**
     * Cache duration in seconds
     */
    const CACHE_DURATION = 3600; // 1 hour

    /**
     * Get a setting value
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function getValue(string $key, $default = null)
    {
        // Check cache first
        $cachedValue = Cache::get(self::CACHE_PREFIX . $key);

        if ($cachedValue !== null) {
            return $cachedValue;
        }

        // Query from database
        $setting = self::where('key', $key)->first();

        if (!$setting) {
            return $default;
        }

        // Store in cache
        Cache::put(self::CACHE_PREFIX . $key, $setting->value, self::CACHE_DURATION);

        return $setting->value;
    }

    /**
     * Set a setting value
     *
     * @param string $key
     * @param mixed $value
     * @return bool
     */
    public static function setValue(string $key, $value): bool
    {
        $setting = self::updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );

        // Update cache
        Cache::put(self::CACHE_PREFIX . $key, $value, self::CACHE_DURATION);

        return $setting->wasRecentlyCreated || $setting->wasChanged('value');
    }

    /**
     * Get all settings as key-value pairs
     *
     * @return array
     */
    public static function getAllSettings(): array
    {
        $settings = self::all()->pluck('value', 'key')->toArray();

        // Cache all settings
        foreach ($settings as $key => $value) {
            Cache::put(self::CACHE_PREFIX . $key, $value, self::CACHE_DURATION);
        }

        return $settings;
    }

    /**
     * Forget a setting from cache
     *
     * @param string $key
     * @return void
     */
    public static function forgetCache(string $key): void
    {
        Cache::forget(self::CACHE_PREFIX . $key);
    }

    /**
     * Flush all settings from cache
     *
     * @return void
     */
    public static function flushCache(): void
    {
        $settings = self::all();

        foreach ($settings as $setting) {
            Cache::forget(self::CACHE_PREFIX . $setting->key);
        }
    }
}
