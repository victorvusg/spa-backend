<?php

namespace App\Repositories;

use App\Service;
use App\Variant;
use App\ServiceCategory;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ServiceRepository implements ServiceRepositoryInterface
{
    public function create(array $attributes = [])
    {
        DB::beginTransaction();
        try {
            $service = $this->save($attributes, false);
            DB::commit();
            return $service;
        } catch (\Exception $e) {
            DB::rollBack();
            throw new \Exception($e->getMessage());
        }
    }

    public function save($data, $is_update, $id = null)
    {
        if ($is_update) {
            $service = Service::find($id);
        } else {
            $service = new Service();
        }
        foreach ($data as $key => $value) {
            if ($key !== 'variants') {
                $service->$key = $value;
            }
        }

        if ($service->save()) {
            $variants = $data['variants'];
            foreach ($variants as $key => $variant) {
                $variants[$key]['service_id'] = $service->id;
                $variants[$key]['created_at'] = Carbon::now();
                $variants[$key]['updated_at'] = Carbon::now();
                $variants[$key]['price'] = isset($variants[$key]['price']) ? $variants[$key]['price'] : 0;
                $variants[$key]['gender'] = isset($variants[$key]['gender']) ? $variants[$key]['gender'] : 'both';
                $variants[$key]['description'] = isset($variants[$key]['description']) ? $variants[$key]['description'] : null;
                $variants[$key]['name'] = isset($variants[$key]['name']) ? $variants[$key]['name'] : null;
                $variants[$key]['is_free'] = isset($variants[$key]['is_free']) ? $variants[$key]['is_free'] : 0;
                $variants[$key]['commission_rate'] = isset($variants[$key]['commission_rate']) ? $variants[$key]['commission_rate'] : 0;
                $variants[$key]['is_active'] = 1;
                $category = ServiceCategory::find($service->service_category_id);
                $variant_category = 'other';
                if ($category) {
                    $variant_category = $category->name;
                }
                $variants[$key]['variant_category'] = isset($variants[$key]['variant_category']) ? $variants[$key]['variant_category'] : $variant_category;
            }
            $variants_inserted = Variant::insert($variants);
            // Return Intake with order
            if ($variants_inserted) {
                $query= new Variant();
                $created_variants = $query->where('service_id', $service->id)->get()->toArray();
                $service->variants = $created_variants;
            }
        } else {
            return false;
        }
        return $service;
    }

    public function get()
    {
        return Service::with('serviceCategory')->get()->toArray();
    }


    public function getOneBy($by, $value)
    {
        return Service::where($by, '=', $value)->first();
    }

    public function update($id, array $attributes = [])
    {
        DB::beginTransaction();
        try {
            $service = $this->save($attributes, true, $id);
            DB::commit();
            return $service;
        } catch (\Exception $e) {
            DB::rollBack();
            throw new \Exception($e->getMessage());
        }
    }

    public function delete($id)
    {
        DB::beginTransaction();
        try {
            $count = Service::destroy($id);
            DB::commit();
            if ($count === 0) {
                throw new \Exception("Service not found");
            }
        } catch (\Exception $e) {
            DB::rollBack();
            throw new \Exception($e->getMessage());
        }
    }
}
