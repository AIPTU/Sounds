<?php

declare(strict_types=1);

/**
 * Converts the sound name (e.g., step.stem) to a constant name (e.g., STEP_STEM).
 */
function soundNameToConstCase(string $name): string
{
    return strtoupper(str_replace(['.', '-'], '_', $name));
}

/**
 * Used to convert file paths to corresponding sound names.
 *
 * @param string $name The file path to be converted.
 * @return string The converted sound name.
 * @example
 * $file = "sounds/step/wood6.fsb";
 * $name = slashToPeriods($file);
 * assert($name === "step.wood6");
 */
function slashToPeriods(string $name): string
{
    $name = preg_replace('/.*sounds\//', '', $name);
    $name = preg_replace('/\..*$/', '', $name);
    return str_replace('/', '.', $name);
}

/**
 * Retrieves all the sound names from bedrock-samples.zip.
 *
 * @return string[] An array of sound names.
 */
function soundNames(): array
{
    $zip = new ZipArchive();
    $zip->open(__DIR__ . '/bedrock-samples.zip');

    // Files that won't be ignored for having a number
    $exceptions = [
        'random.pop2',
        'item.trident.riptide_1',
        'item.trident.riptide_2',
        'item.trident.riptide_3',
        'record.11',
        'record.13',
    ];

    $return = [];
    for ($i = 0; $i < $zip->numFiles; $i++) {
        $name = $zip->getNameIndex($i);
        if (preg_match('/sounds\/.*\./', $name)) {
            $name = slashToPeriods($name);
            if (!in_array($name, $exceptions, true) && preg_match('/\d$/', $name)) {
                if (!preg_match('/1$/', $name)) {
                    continue;
                }
                $name = substr($name, 0, -1);
            }
            $return[] = $name;
        }
    }
    $zip->close();
    return $return;
}

$sounds = soundNames();

$soundIdsContent = '<?php

declare(strict_types=1);

namespace aiptu\sounds;

class SoundIds
{
';
$vanillaSoundsContent = '<?php

declare(strict_types=1);

namespace aiptu\sounds;

class VanillaSounds
{
';

foreach ($sounds as $sound) {
    $constSound = soundNameToConstCase($sound);
    $soundIdsContent .= "    public const $constSound = '$sound';\n";
    $vanillaSoundsContent .= "
    /**
     * Creates a new instance of SoundImpl for the $sound sound.
     *
     * @return SoundImpl The created SoundImpl instance.
     */
    public static function $constSound(): SoundImpl
    {
        return new SoundImpl(SoundIds::$constSound);
    }\n";
}

$soundIdsContent .= "}\n";
$vanillaSoundsContent .= "}\n";

file_put_contents(__DIR__ . '/src/aiptu/sounds/SoundIds.php', $soundIdsContent);
file_put_contents(__DIR__ . '/src/aiptu/sounds/VanillaSounds.php', $vanillaSoundsContent);
