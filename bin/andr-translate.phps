#!/usr/bin/env bash
# Author: Andrew Chen <yongjhih@gmail.com>
# CC, Creative Commons
# Author: Andrew Chen <yongjhih@gmail.com>
# CC, Creative Commons
# Author: Andrew Chen <yongjhih@gmail.com>
# CC, Creative Commons
#! Mon., 11-24 09:19:13 CST 2010
# Author: Andrew Chen <yongjhih@gmail.com>
# CC, Creative Commons
# Author: Andrew Chen <yongjhih@gmail.com>
# CC, Creative Commons
# Author: Andrew Chen <yongjhih@gmail.com>
# CC, Creative Commons
# TODO replace by specific field line by line


if [ ! "$2" ]; then
	echo "$0 <input_file> <target_file> [reference_file]"
	exit 1
fi

SOURCE="$1"
DESTINATION="$2"
IMPORT="$3"

if [ -f "/tmp/destination.name.list" ]; then
	rm -f -- "/tmp/destination.name.list"
fi

cat "$SOURCE" | tr '\r\n' ' ' |
# remove commentation
sed 's/-->/&\n/g' |
sed 's/<!--.*-->//g' |
sed 's/\(<string[^>]*>\)\s*/\n\1/g' |
sed 's/\s*\(<\/string>\)/\1\n/g' |
while read line; do
	#echo "$line" | grep '<!--[^>]*-->' > /dev/null
	#if [ "$?" == "0" ]; then # match
		#continue
	#fi
	echo "$line" | grep '^\s*<string[^>]*name="\([^"]*\)"[^>]*>.*</string>\s*' > /dev/null
	if [ "$?" != "0" ]; then # not match
		continue
	fi
	echo "$line" | grep 'translatable="false"' > /dev/null
	if [ "$?" == "0" ]; then # match
		continue
	fi
	echo "$line" | grep '^\s*<string[^>]*name="\([^"]*_default\)"[^>]*>.*</string>\s*' > /dev/null
	if [ "$?" == "0" ]; then # match
		continue
	fi
	echo "$line" | grep '^\s*<string[^>]*name="\([^"]*_value_[^"]*\)"[^>]*>.*</string>\s*' > /dev/null
	if [ "$?" == "0" ]; then # match
		continue
	fi

	STRING_NAME="`echo "$line" | sed 's#^\s*<string[^>]*name="\([^"]*\)"[^>]*>.*</string>\s*#\1#'`"

	grep '^\s*<string[^>]*name="'"$STRING_NAME"'\([^"]*\)"[^>]*>.*</string>\s*' "$DESTINATION" > /dev/null
	if [ "$?" == "0" ]; then # match
		continue
	fi

	echo "$line" | sed -e 's#^\s*<#    &!-- #' -e 's#>\s*$# --&#' >> "/tmp/destination.name.list"
done

if [ ! -f "/tmp/destination.name.list" ]; then
	echo "/tmp/destination.name.list not found"
	exit 1
fi
echo "</resources>" >> "/tmp/destination.name.list"

sed '/<\/resources>/,$d' "$DESTINATION" > "/tmp/destination.name.list.top"
cat "/tmp/destination.name.list.top" "/tmp/destination.name.list" > "/tmp/destination.name.list.whole"

mv "$DESTINATION" "/tmp/$$.`basename $DESTINATION`"
mv "/tmp/destination.name.list.whole" "$DESTINATION"

# import & relace
if [ ! -f "$IMPORT" ]; then
	exit 0
fi

cat "$IMPORT" | tr '\r\n' ' ' |
sed -e 's/^\s*//g' -e 's/\s*$//g' |
sed 's/-->/&\n/g' |
sed 's/<!--.*-->//g' |
sed 's/\(<string[^>]*>\)\s*/\n\1/g' |
sed 's/\s*\(<\/string>\)/\1\n/g' |
while read line; do
	echo "$line" | grep '^\s*<string[^>]*name="\([^"]*\)"[^>]*>.*</string>\s*' > /dev/null
	if [ "$?" != "0" ]; then # not match
		continue
	fi
	STRING_NAME="`echo "$line" | sed 's#^\s*<string[^>]*name="\([^"]*\)"[^>]*>.*</string>\s*#\1#'`"
	STRING_VALUE="`echo "$line" | sed 's#^\s*<string[^>]*name="\([^"]*\)"[^>]*>\(.*\)</string>\s*#\2#'`"
	sed -i 's#\s*<!--\s*\(string[^>]*name="\)'"$STRING_NAME"'\("[^>]*>\).*\(</string\)\s*-->\s*'"#    <\1$STRING_NAME\2$STRING_VALUE</string>#" "$DESTINATION"
done

exit 0

