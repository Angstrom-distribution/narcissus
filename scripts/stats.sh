
echo > packages.count
cat *.txt > packages.list
packagelist="$(for entry in $(cat packages.list) ; do echo $entry ; done | sort | uniq | xargs echo)"

for package in $packagelist ; do 
	echo "$(cat packages.list | grep $package | wc -l) $package" >> packages.count
done

cat packages.count | sort -rn

