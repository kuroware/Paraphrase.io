import json
from pprint import pprint

AllPythonFunctions = []


with open('data_types.json') as data_file:
	data = json.load(data_file)
for i in range(0, len(data['entries']) - 1):
	if data['entries'][i]['type'] == 'Data Types':
		if data['entries'][i]['type'] and data['entries'][i]['name'] and data['entries'][i]['path']:
			pythonFunction = {
				'name': data['entries'][i]['name'],
				'path': data['entries'][i]['path'],
				'type': data['entries'][i]['type']
			}
			AllPythonFunctions.append(pythonFunction)
JSONPython = json.dumps(AllPythonFunctions, indent = 4)
print(JSONPython)