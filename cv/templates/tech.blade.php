@php
$theme = [
  'accent' => '#0ea5e9',
  'font' => 'DejaVu Sans',
  'layout' => 'two',
  'order' => ['summary','projects','experience','education','additional'],
];
@endphp
@include('cv.templates._base', ['cv'=>$cv, 'theme'=>$theme])
