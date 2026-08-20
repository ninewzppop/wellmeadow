@extends('layouts.app')

@section('title', $title)

@section('content')
    <div class="rounded-lg border border-dashed border-slate-300 bg-white p-12 text-center">
        <h1 class="text-xl font-semibold text-slate-700">{{ $title }}</h1>
        <p class="mt-2 text-sm text-slate-500">
            หน้านี้ยังอยู่ระหว่างพัฒนา — route และเมนูเตรียมไว้แล้วจากโครงสร้างฐานข้อมูลจริง
        </p>
    </div>
@endsection