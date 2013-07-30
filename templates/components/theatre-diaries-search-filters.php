<div class="row">
	<div class="large-12 column">
		<h2>Search schedules by:</h2>
	</div>
</div>
<div class="row search-filters theatre-diaries">

	<div class="large-12 column">

		<table class="grid">
			<thead>
				<tr>
					<th>Site:</th>
					<th>Theatre:</th>
					<th>Subspeciality:</th>
					<th>Firm:</th>
					<th>Ward:</th>
					<th>Emergency list:</th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td>
						<select>
							<option>All sites</option>
						</select>
					</td>
					<td>
						<select>
							<option>All theatres</option>
						</select>
					</td>
					<td>
						<select>
							<option>All specialities</option>
						</select>
					</td>
					<td>
						<select>
							<option>All firms</option>
						</select>
					</td>
					<td>
						<select>
							<option>All wards</option>
						</select>
					</td>
					<td>
						<input type="checkbox" />
					</td>
				</tr>
			</tbody>
		</table>

		<div class="search-filters-extra">

			<label class="highlight">
				<input type="radio" name="date-filter" />
				Today
			</label>

			<label class="highlight">
				<input type="radio" name="date-filter" />
				Next 7 days
			</label>

			<label class="highlight">
				<input type="radio" name="date-filter" />
				Next 30 days
			</label>

			<fieldset class="highlight">
				<label>
					<input type="radio" name="date-filter" />
					or select date range:
				</label>

				<input class="small fixed-width" type="text" name="date-start" /> to
				<input class="small fixed-width" type="text" name="date-end" />

				<ul class="button-group">
          <li><a href="#" class="small button">Last week</a></li>
          <li><a href="#" class="small button">Next week</a></li>
        </ul>
			</fieldset>
		</div>
	</div>
</div>