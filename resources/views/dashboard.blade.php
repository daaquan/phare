@extends('layouts.default')

@section('content')

  <h1>@lang('pages.users.title')</h1>

  <p>@lang('pages.users.description')</p>

  <div class="stats stats-vertical lg:stats-horizontal shadow">

    <div class="stat">
      <div class="stat-figure text-primary">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
             class="inline-block w-8 h-8 stroke-current">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>
      </div>
      <div class="stat-title">Downloads</div>
      <div class="stat-value">31K</div>
      <div class="stat-desc">Jan 1st - Feb 1st</div>
    </div>

    <div class="stat">
      <div class="stat-figure text-warning">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
             class="inline-block w-8 h-8 stroke-current">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path>
        </svg>
      </div>
      <div class="stat-title">New Users</div>
      <div class="stat-value">4,200</div>
      <div class="stat-desc">↗︎ 400 (22%)</div>
    </div>

    <div class="stat">
      <div class="stat-figure text-success">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
             class="inline-block w-8 h-8 stroke-current">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path>
        </svg>
      </div>
      <div class="stat-title">New Registers</div>
      <div class="stat-value">1,200</div>
      <div class="stat-desc">↘︎ 90 (14%)</div>
    </div>

  </div>

  <div class="my-10"></div>

  <div role="tablist" class="tabs tabs-lifted text-nowrap">
    <input type="radio" name="my_tabs_2" role="tab" class="tab" aria-label="ユーザID"/>
    <div role="tabpanel" class="tab-content bg-base-100 border-base-300 rounded-box p-6"></div>

    <input type="radio" name="my_tabs_2" role="tab" class="tab" aria-label="ユーザID以外の要素" checked/>
    <div role="tabpanel" class="tab-content bg-base-100 border-base-300 rounded-box p-6">
      <div class="overflow-x-auto">
        <table class="table m-0">
          <!-- head -->
          <thead>
          <tr>
            <th></th>
            <th>Name</th>
            <th>Job</th>
            <th>Favorite Color</th>
          </tr>
          </thead>
          <tbody>
          <!-- row 1 -->
          <tr>
            <th>1</th>
            <td>Cy Ganderton</td>
            <td>Quality Control Specialist</td>
            <td>Blue</td>
          </tr>
          <!-- row 2 -->
          <tr>
            <th>2</th>
            <td>Hart Hagerty</td>
            <td>Desktop Support Technician</td>
            <td>Purple</td>
          </tr>
          <!-- row 3 -->
          <tr>
            <th>3</th>
            <td>Brice Swyre</td>
            <td>Tax Accountant</td>
            <td>Red</td>
          </tr>
          </tbody>
        </table>
      </div>
    </div>

    <input type="radio" name="my_tabs_2" role="tab" class="tab" aria-label="決済情報"/>
    <div role="tabpanel" class="tab-content bg-base-100 border-base-300 rounded-box p-6"></div>
  </div>

@endsection
