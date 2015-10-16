function TimeTable(parent) {	
	var ROW_TEMPLATE = "<td class='timetable-timeslot :pos' row-id=':rid' col-id=':cid'></td>";

	//Main Time Table structure
	$(parent).append(
		"<table id='table-timetable' class='table-timetable' style='width: 100%'> \
			<tr style='height: 40px'> \
				<th style='width: 8%'></th> \
				<th>Mon</th> \
				<th>Tue</th> \
				<th>Wed</th> \
				<th>Thu</th> \
				<th>Fri</th> \
				<th>Sat</th> \
				<th>Sun</th> \
			</tr> \
		</table>"
		);

	this.timetable = $(parent).find("#table-timetable");
	this.ontoggle = undefined;

	var timeMark = this.TIME_START;	
	var _this_ = this;

	//Add Time slot cell
	for (i = 0; i < (this.TIME_END - this.TIME_START) * this.TIME_DIVISION; ++i) {
		if (i % this.TIME_DIVISION == 0) {
			var row = "<td style='text-align: center' rowspan ='" + this.TIME_DIVISION + "'>" + timeMark;

			if (timeMark == this.TIME_START)
				row += " <sup>AM</sup>";
			else if (timeMark == 12 || timeMark == this.TIME_END - 13)
				row += " <sup>PM</sup>";

			row += "</td>";

			++timeMark;
			if (timeMark > 12) timeMark = 1;
		} else row = "";

		for (j = 0; j < 7; ++j)
			row += ROW_TEMPLATE.replace(":pos", i % this.TIME_DIVISION == 0 ? "td-firstrow" : "td-middlerow").replace(":rid", i).replace(":cid", j);

		$(this.timetable).append("<tr>" + row + "</tr>");
	}

	//Timeslot click event: Toggle free time
	$(this.timetable).find(".timetable-timeslot").on("click", function() {
		$(this).toggleClass('unavailable');

		//Fire Event OnToggle
		if (_this_.ontoggle != undefined)			
			_this_.ontoggle(parseInt($(this).attr("row-id")), parseInt($(this).attr("col-id")), $(this).hasClass('unavailable'));
	});
}


//Constants
TimeTable.prototype.TIME_DIVISION = 2;
TimeTable.prototype.TIME_START = 9;
TimeTable.prototype.TIME_END = 20;
TimeTable.prototype.DIVISION = (TimeTable.prototype.TIME_END - TimeTable.prototype.TIME_START) * TimeTable.prototype.TIME_DIVISION;


//Methods
TimeTable.prototype.assignClassTime = function(weekday, from, to, content, color) {
	for (i = from ; i <= to; ++ i) {
		var period = $(this.timetable).find("[col-id='" + weekday + "'][row-id='" + i + "']");
		if ($(period).attr("rowspan") != undefined || $(period).css("display") == "none" || $(period).hasClass('unavailable')) return ;
	}

	var firstRow = $(this.timetable).find("[col-id='" + weekday + "'][row-id='" + from + "']");	
	firstRow.html(content);
	firstRow.attr("rowspan", to - from + 1);

	if (color != undefined) firstRow.css("background", color);

	for (i = from + 1; i <= to; ++ i)
		$(this.timetable).find("[col-id='" + weekday + "'][row-id='" + i + "']").hide();
}

TimeTable.prototype.unassignClassTime = function(weekday, from) {
	var firstRow = $(this.timetable).find("[col-id='" + weekday + "'][row-id='" + from + "']");
	if (firstRow.css("display") == "none" || firstRow.attr("rowspan") == undefined || firstRow.hasClass('unavailable')) return ;

	var span = firstRow.attr("rowspan");

	firstRow.removeAttr("rowspan");
	firstRow.removeAttr("style");
	firstRow.html("");

	for (i = 1; i < span; ++i)
		$(this.timetable).find("[col-id='" + weekday + "'][row-id='" + parseInt(from + i) + "']").show();
}
