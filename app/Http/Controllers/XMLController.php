<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use App\Models\XMLFile;

class XMLController extends Controller
{
    protected bool $syntax_error = FALSE;

    function getJson($id)
    {
        $xml = XMLFile::find($id);
        $json = json_encode($xml);
        return $json;
    }

    function index()
    {
        $xmls = XMLFile::all();
        return view('xml.index', compact('xmls'));
    }

    public function upload(Request $request)
    {
        try {
            $request->validate([
                'file' => 'required|file|mimes:text/xml,xml,application/xml',
            ]);
        } catch (ValidationException  $e) {
            return back()->with('error', 'Ошибка загрузки: ' . $e->getMessage());
        }
        if ($request->file('file')->isValid()) {
            $filePath = $request->file('file')->store('uploads', 'public');
            $found = $this->checkFile($filePath);

            if (!$found) {
                $msg = '[паттерн не найден]';
            }

            if ($this->syntax_error) {
                $msg .= ' [синтаксическая ошибка]';
                return back()->with('error', $msg);
            }

            if ($found) {
                $this->addFile($request->file('file'), $filePath);
                $msg = '[сохранено]';
                return back()->with('success', 'Загружено ОК ' . $msg)->with('file', $filePath);
            }
        }
        return back()->with('error', $msg);
    }

    function checkFile($filePath): bool
    {
        $data = Storage::disk('public')->get($filePath);

        try {
            $xml = simplexml_load_string($data);
            $json = json_encode($xml);
            $array = json_decode($json, TRUE);
        } catch (\Exception $e) {
            $this->syntax_error = TRUE;
            return false;
        }

        return $this->parseXML($array);
    }

    function checkComponent($components, &$found = false)
    {
        $found_id = false;
        $found_error = false;
        $found_limit = false;
        $found_value = false;
        $correct_length = false;
        //dump($components);
        foreach ($components as $component) {
            $attrs = $component['@attributes'];
            foreach ($attrs as $attrName => $attrValue) {
                if (strtolower($attrName) == 'id' && $attrValue == '030-032-000-000') {
                    $found_id = true;
                }
            }
            foreach ($component as $prop => $val) {
                if (strtolower($prop) == 'value' && is_array($val) && count($val) == 0) {
                    $found_value = true;
                }
                if (strtolower($prop) == 'limit' && is_array($val) && count($val) == 0) {
                    $found_limit = true;
                }
                if (strtolower($prop) == 'error' && strtolower($val) == 'error') {
                    $found_error = true;
                }
            }

            if (count($component) == 4) {
                $correct_length = true;
            }
        }

        if ($found_id && $found_value && $found_limit && $found_error && $correct_length) {
            $found = true;
        }
        //dump($found);
    }

    function parseXML($array, &$found = false): bool
    {
        foreach ($array as $key => $value) {
            if (strtolower($key) == 'component' && is_array($value)) {
                $this->checkComponent($value, $found);
                if ($found) {
                    break;
                }
            } else if (is_array($value)) {
                $this->parseXML($value, $found);
            }
        }

        return $found;
    }

    function addFile($file, $filePath): bool
    {
        $data = Storage::disk('public')->get($filePath);
        $fileName = $file->getClientOriginalName();

        $xmlf = XMLFile::create([
            'file_name' => $fileName,
            'file_id' => $filePath,
            'content' => $data,
        ]);

        return true;
    }
}
