#!/usr/bin/python

import os, sys, csv, numpy, pprint, MySQLdb, getopt, ConfigParser, json, copy
from sklearn.cluster import *
from sklearn.cluster.hierarchical import *
from sklearn import metrics
from sklearn.datasets.samples_generator import make_blobs
from sklearn.neighbors import kneighbors_graph
from hcluster import pdist, linkage, dendrogram

def dbConnect(server, dbuser, dbpassword, dbname):
	return MySQLdb.connect(server, dbuser, dbpassword, dbname)

def getInputData(dbconn, feature_set, infile, incluster):
	cursor = dbconn.cursor()
	cursor.execute("SELECT * FROM AnalyticsBaseTable")
	results = cursor.fetchall()
	num_fields = len(cursor.description)
	field_names = [i[0] for i in cursor.description]

	used_fields = []
	if feature_set == 2: # all features
		used_fields = copy.copy(field_names)
		used_fields.remove("Cust_ID")
	elif feature_set == 1: # transaction types
		for field_name in field_names:
			if 'TransTypePerMonth_' in field_name and field_name != 'TransTypePerMonth_TOTAL':
				used_fields.append(field_name)
	else: #rfm
		#used_fields = ['Age', 'LifeTime', 'AvgTransValue', 'TimeSinceLastTrans', 'TransTypePerMonth_TOTAL']
		used_fields = ['AvgTransValue', 'TimeSinceLastTrans', 'TransTypePerMonth_TOTAL']

	if infile != '':
		indata = json.loads(open(infile).read())

		incustomers = []
		for incustid in indata['clusters'][incluster]:
			incustomers.append(incustid)

	custids = []
	datamatrix = {}
	completedatamatrix = []
	alldatamatrix = {}
	dataArr = []
	for row in results:
		dataline = []
		tempCustId = 0
		for (x, value) in enumerate(field_names):
			if field_names[x] in used_fields:
				field_value = row[x]
				if field_names[x] == 'Gender':
					if field_value == 'Male':
						field_value = 0
					else:
						field_value = 1
				dataline.append(float(field_value))
			if field_names[x] == 'Cust_ID':
				tempCustId = int(round(row[x],0))
				
		#print "Number:%d New number: %d\n" % (( row[x]),int(round(row[x],0)))
		if infile == '' or tempCustId in incustomers:
			custids.append(tempCustId)
			dataArr.append(dataline)

			datamatrix[tempCustId] = []
			datamatrix[tempCustId].append(dataline)

		alldatamatrix[tempCustId] = []
		alldatamatrix[tempCustId].append(dataline)
		completedatamatrix.append(dataline)

	return datamatrix, numpy.array(dataArr), custids, used_fields, numpy.array(completedatamatrix), alldatamatrix

def normalizeData(data, alldata):
#	newdata = numpy.array(data)
	minimums = numpy.min(alldata, axis=0)
	maximums = numpy.max(alldata, axis=0)
	averages = numpy.mean(alldata, axis=0)
	stddevs = numpy.std(alldata, axis=0)
	medians = numpy.median(alldata, axis=0)

	# removing outliers greater than 'n_stddev' times the standard deviation
	n_stddev = 3
	maximumsnooutliers = []
	for index, value in enumerate(maximums):
		if value > (averages[index] + n_stddev * stddevs[index]):
			maximumsnooutliers.append(averages[index] + n_stddev * stddevs[index])
		else:
			maximumsnooutliers.append(value)

	minimumsnooutliers = []
	for index, value in enumerate(minimums):
		if value < (averages[index] - n_stddev * stddevs[index]):
                        minimumsnooutliers.append(averages[index] - n_stddev * stddevs[index])
                else:
                        minimumsnooutliers.append(value)

	# normalize input data from computed average and std devs, after having removed outliers
	norm_data = []
	for dataline in data:
		normdataline = []
		for index, featvalue in enumerate(dataline):
			if (featvalue > maximumsnooutliers[index]):
				normdataline.append(1.00)
			elif (featvalue < minimumsnooutliers[index]):
				normdataline.append(0.00)
			elif ((maximumsnooutliers[index] - minimumsnooutliers[index]) == 0):
				normdataline.append(1)
			else:
				normdataline.append((featvalue - minimumsnooutliers[index]) / (maximumsnooutliers[index] - minimumsnooutliers[index]))
		norm_data.append(normdataline)

	datastats = {'averages': averages, 'medians': medians, 'stddevs': stddevs, 'maximums': maximums, 'minimums': minimums}

	return norm_data, datastats

def runClusterer(data, clusterer, n_clusters):
	if clusterer == 0:
		estimator = KMeans(n_clusters)
	elif clusterer == 1:
		estimator = MiniBatchKMeans(n_clusters)
	elif clusterer == 2:
		estimator = Ward(n_clusters)#, compute_full_tree = True)

	clusters = estimator.fit_predict(data)

        #estimator = MeanShift(bandwidth=4) # not working
        #estimator = SpectralClustering(n_clusters=8) # not working

	return estimator, clusters

def getClusterStats(data, labels, cust_ids, fields, averages, stddevs):
	clusters = {}
        clusters_data = {}
	cluster_stats = {}
	cluster_stats['population'] = {}
	cluster_stats['population']['size'] = 0
	for (i, dataline) in enumerate(data):
		label = labels[i]
		cust_id = cust_ids[i]
		if not label in cluster_stats:
			clusters[label] = []
			clusters_data[label] = {}
			cluster_stats[label] = {}
			cluster_stats[label]['size'] = 0

		clusters[label].append(cust_id)
		#clusters_data[label].append(dataline)

		cluster_stats[label]['size'] += 1
		cluster_stats['population']['size'] += 1

		for (j, value) in enumerate(dataline):
			field_name = fields[j]
#			if not field_name in cluster_stats[label]:
#				cluster_stats[label][field_name] = value
#			else:
#				cluster_stats[label][field_name] += value
			if not field_name in clusters_data[label]:
				clusters_data[label][field_name] = []
			clusters_data[label][field_name].append(value)
			if not field_name in cluster_stats['population']:
				cluster_stats['population'][field_name] = value
			else:
				cluster_stats['population'][field_name] += value

	for cluster in cluster_stats:
		if cluster != 'population':
			for name in fields:
				if name != 'size':
#					cluster_stats[cluster][name] = float(cluster_stats[cluster][name])
#					cluster_stats[cluster][name] /= cluster_stats[cluster]['size'] 
					cluster_stats[cluster][name] = {}
					cluster_stats[cluster][name]['average'] = numpy.mean(clusters_data[cluster][name])
					cluster_stats[cluster][name]['median'] = numpy.median(clusters_data[cluster][name])
					cluster_stats[cluster][name]['max'] = numpy.max(clusters_data[cluster][name])
					cluster_stats[cluster][name]['min'] = numpy.min(clusters_data[cluster][name])
					cluster_stats[cluster][name]['stddev'] = numpy.std(clusters_data[cluster][name])

	cluster_zscores = {}
	for cluster in cluster_stats:
		if cluster != 'population':
			cluster_zscores[cluster] = {}
			for (id, name) in enumerate(cluster_stats[cluster]):
				if name == 'size':
					n_clusters = len(cluster_stats) - 1
					avgsize = cluster_stats['population']['size'] / n_clusters
					cluster_zscores[cluster]['size'] = cluster_stats[cluster]['size'] - avgsize
				else:
					cluster_zscores[cluster][name] = (cluster_stats[cluster][name]['average'] - averages[fields.index(name)]) / stddevs[fields.index(name)]

	return clusters, cluster_stats, cluster_zscores

def writeOutputFile(outfile, silhouette, n_clusters, fields, clusters, cluster_stats, cluster_zscores, data, infile, incluster):
	if infile != '': # update existing json file with new subclusters
		indata = json.loads(open(infile).read())
		jsondata = {}
		jsondata['silhouette'] = silhouette
		jsondata['n_clusters'] = n_clusters + indata['n_clusters'] - 1

		n_clusters_prev = len(indata['clusters'])

		jsondata['clusters'] = {}
		for k in range(0, n_clusters_prev):
			if k < int(incluster):
				jsondata['clusters'][str(k)] = indata['clusters'][str(k)]
			elif k > int(incluster):
				newk = k + n_clusters - 1
				jsondata['clusters'][str(newk)] = indata['clusters'][str(k)]
		for k in range(0, n_clusters):
			newk = k + int(incluster)
			jsondata['clusters'][str(newk)] = clusters[k]

		jsondata['features'] = fields

		jsondata['cluster_stats'] = {}
                jsondata['cluster_stats']['population'] = indata['cluster_stats']['population']
		for k in range(0, n_clusters_prev):
                        if k < int(incluster):
                                jsondata['cluster_stats'][str(k)] = indata['cluster_stats'][str(k)]
                        elif k > int(incluster):
                                newk = k + n_clusters - 1
                                jsondata['cluster_stats'][str(newk)] = indata['cluster_stats'][str(k)]
                for k in range(0, n_clusters):
                        newk = k + int(incluster)
                        jsondata['cluster_stats'][str(newk)] = cluster_stats[k]

		jsondata['cluster_zscores'] = {}
		for k in range(0, n_clusters_prev):
                        if k < int(incluster):
                                jsondata['cluster_zscores'][str(k)] = indata['cluster_zscores'][str(k)]
                        elif k > int(incluster):
                                newk = k + n_clusters - 1
                                jsondata['cluster_zscores'][str(newk)] = indata['cluster_zscores'][str(k)]
                for k in range(0, n_clusters):
                        newk = k + int(incluster)
                        jsondata['cluster_zscores'][str(newk)] = cluster_zscores[k]
	else:
		jsondata = {}
		jsondata['silhouette'] = silhouette
		jsondata['n_clusters'] = n_clusters
		jsondata['clusters'] = clusters
		jsondata['features'] = fields
		jsondata['cluster_stats'] = cluster_stats
		jsondata['cluster_zscores'] = cluster_zscores

	with open(outfile, 'w') as output:
		json.dump(jsondata, output)

	# datadict = {}
	# datacount = 0
	# for datarow in data:
		# datacount += 1
		# datadict[datacount] = []
		# for datavalue in datarow:
			# datadict[datacount].append(datavalue)
			
	with open(outfile.replace('.json', '-data.json'), 'w') as output:
		json.dump(data, output)

def printHelp(scriptname, n_clusters, outfile):
	print '\n', '  ' + scriptname + ' usage help:'
	print '\n', '  python ' + scriptname + ' -c <clustering_method> -k <number_of_clusters> -f <set_of_features> -o <output_filename> [-i <input_filename> -n <input_cluster>]'
	print '\n', '  Accepted values for <clustering_method> and their meanings:'
	print '     0: K-Means (default)'
	print '     1: MiniBatch K-Means'
	print '     2: Hierarchical Clustering / Ward'
	print '\n', '  <number_of_clusters> is set to %d by default' % (n_clusters)
	print '\n', '  Accepted values for <set_of_features> and their meanings'
	print '     0: RFM - Recency Frequency Monetary value (default)'
	print '     1: Transaction types'
	print '     2: All features'
        print '\n', '  <output_filename> is set to %s by default' % (outfile)
	print '\n', '  <input_filename> and <input_cluster> need not be specified, except when creating subclusters for the input cluster'
	print '\n'

def main(argv):
	scriptname = os.path.basename(__file__)

	# config
	Config = ConfigParser.ConfigParser()
	Config.read('./config.ini')
	server = Config.get('DBconfig', 'Server')
	dbuser = Config.get('DBconfig', 'Username')
	dbpassword = Config.get('DBconfig', 'Password')

	# defaults
	clusterer = 0
	feature_set = 0
	n_clusters = 5
	outfile = 'output.json'
	dbname = 'ceader_clusterer'
	infile = ''
	incluster = -1

	try:
		opts, args = getopt.getopt(argv,"hd:c:k:f:o:i:n:")
	except getopt.GetoptError:
		printHelp(scriptname, n_clusters, outfile)
		sys.exit(2)

	for opt, arg in opts:
		if opt == '-h':
			printHelp(scriptname, n_clusters, outfile)
			sys.exit(2)
		elif opt == '-d':
			dbname = arg
		elif opt == '-c':
			clusterer = int(arg)
			if clusterer < 0 or clusterer > 2:
				printHelp(scriptname, n_clusters, outfile)
				sys.exit(2)
		elif opt == '-k':
			n_clusters = int(arg)
		elif opt == '-f':
			feature_set = int(arg)
			if feature_set < 0 or feature_set > 2:
				printHelp(scriptname, n_clusters, outfile)
				sys.exit(2)
		elif opt == '-o':
			outfile = arg
		elif opt == '-i':
			infile = arg
		elif opt == '-n':
			incluster = arg


	dbconn = dbConnect(server, dbuser, dbpassword, dbname)

	data, cleanData, cust_ids, fields, alldata, alldatamatrix = getInputData(dbconn, feature_set, infile, incluster)
	norm_data, datastats = normalizeData(cleanData, alldata)

	if n_clusters == -1: # find optimal k
		maxsilhouette = -1
		optk = 0
		for nc in range(2, 10):
			norm_estimator, norm_labels = runClusterer(norm_data, clusterer, nc)
			norm_silhouette = metrics.silhouette_score(norm_data, norm_labels, metric='euclidean')
			if norm_silhouette > maxsilhouette:
				maxsilhouette = norm_silhouette
				optk = nc
		n_clusters = optk

	norm_estimator, norm_labels = runClusterer(norm_data, clusterer, n_clusters)
	norm_clusters, norm_cluster_stats, norm_cluster_zscores = getClusterStats(cleanData, norm_labels, cust_ids, fields, datastats['averages'], datastats['stddevs'])
	norm_silhouette = metrics.silhouette_score(norm_data, norm_labels, metric='euclidean')

	writeOutputFile(outfile, norm_silhouette, n_clusters, fields, norm_clusters, norm_cluster_stats, norm_cluster_zscores, alldatamatrix, infile, incluster)

# BEGIN Testing Ward
#	if clusterer == 2:
#		for node, child in enumerate(estimator.children_):
#			print node, ': ', child[0], ' & ', child[1]
# END Testing Ward

if __name__ == "__main__":
   main(sys.argv[1:])
