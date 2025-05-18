<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Google\Cloud\Translate\V3\Client\TranslationServiceClient as ClientTranslationServiceClient;
use Google\Cloud\Translate\V3\TranslateTextRequest;

class TranslationController extends Controller
{
    protected $translate;
    protected $projectId;
    protected $location;

    public function __construct()
    {
        // TranslationServiceClient will automatically use the GOOGLE_APPLICATION_CREDENTIALS env variable
        $this->translate = new ClientTranslationServiceClient();
        $this->projectId = env('GOOGLE_PROJECT_ID');
        $this->location = 'global'; // or 'us-central1' if that's your project location
    }

    public function translate(Request $request)
    {
        $validated = $request->validate([
    'q' => 'required|string',
    'source' => 'required|string',
    'target' => 'required|string',
]);

try {
    $parent = $this->translate->locationName($this->projectId, $this->location);

    $contents = [$validated['q']];
    $targetLanguage = $validated['target'];
    $sourceLanguageCode = $validated['source'];

    $request = new TranslateTextRequest();
    $request->setParent($parent);
    $request->setContents($contents);
    $request->setTargetLanguageCode($targetLanguage);
    $request->setSourceLanguageCode($sourceLanguageCode);

    $response = $this->translate->translateText($request);

    $translatedText = '';
    foreach ($response->getTranslations() as $translation) {
        $translatedText = $translation->getTranslatedText();
    }

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Translation failed',
                'details' => $e->getMessage(),
            ], 500);
        }

        return response()->json([
            'translatedText' => $translatedText,
        ]);
    }
}
