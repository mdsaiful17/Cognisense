@php
$theme = [
  'accent' => '#111827',
  'font' => 'DejaVu Serif',
  'layout' => 'single',
  'order' => ['summary','experience','education','projects','skills','certifications','awards','languages','volunteering','additional'],
];
@endphp
@include('cv.templates._base', ['cv'=>$cv, 'theme'=>$theme])
